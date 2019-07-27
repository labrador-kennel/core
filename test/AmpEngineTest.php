<?php declare(strict_types=1);

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test;

use Amp\Delayed;
use Amp\Success;
use Cspray\Labrador\Application;
use Cspray\Labrador\Engine;
use Cspray\Labrador\AmpEngine;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\CallbackApplication;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Test\Stub\LoadPluginCalledApplication;
use Cspray\Labrador\Test\Stub\NoopApplication;
use Auryn\Injector;
use Cspray\Labrador\AsyncEvent\AmpEmitter;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\Event;
use PHPUnit\Framework\TestCase as UnitTestCase;
use Psr\Log\Test\TestLogger;
use RuntimeException;

class AmpEngineTest extends UnitTestCase {

    /**
     * @var Emitter
     */
    private $emitter;
    /**
     * @var Injector
     */
    private $injector;

    /**
     * @var TestLogger
     */
    private $logger;

    public function setUp() {
        $this->injector = new Injector();
        $this->emitter = new AmpEmitter();
        $this->logger = new TestLogger();
    }

    private function getEngine(Emitter $eventEmitter = null) : AmpEngine {
        $emitter = $eventEmitter ?: $this->emitter;
        $engine = new AmpEngine($emitter);
        $engine->setLogger($this->logger);
        return $engine;
    }

    private function noopApp() : Application {
        return new NoopApplication();
    }

    private function callbackApp(callable $callback) : Application {
        $pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $pluggable->expects($this->once())->method('loadPlugins')->willReturn(new Success());
        return new CallbackApplication($pluggable, $callback);
    }

    private function exceptionHandlerApp(callable $appCallback, callable $handler) : Application {
        $pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $pluggable->expects($this->once())->method('loadPlugins')->willReturn(new Success());
        return new CallbackApplication($pluggable, $appCallback, $handler);
    }

    public function testEventsExecutedInOrder() {
        $data = new \stdClass();
        $data->data = [];
        $bootUpCb = function() use($data) {
            $data->data[] = 1;
            yield new Delayed(0);
            $data->data[] = 2;
            yield new Delayed(0);
            $data->data[] = 3;
            yield new Delayed(0);
        };

        $cleanupCb = function() use($data) {
            $data->data[] = 4;
            yield new Delayed(0);
            $data->data[] = 5;
            yield new Delayed(0);
            $data->data[] = 6;
            yield new Delayed(0);
        };

        $engine = $this->getEngine();
        $engine->onEngineBootup($bootUpCb);
        $engine->onEngineShutdown($cleanupCb);

        $engine->run(new NoopApplication());

        $this->assertSame([1,2,3,4,5,6], $data->data);
    }

    public function testExecuteAppFinishesBeforeAppCleanup() {
        $data = new \stdClass();
        $data->data = [];
        $executeAppCb = function() use($data) {
            $data->data[] = 1;
            yield new Delayed(0);
            $data->data[] = 2;
            yield new Delayed(0);
            $data->data[] = 3;
            yield new Delayed(0);
            return new Success();
        };

        $cleanupCb = function() use($data) {
            $data->data[] = 4;
            yield new Delayed(0);
            $data->data[] = 5;
            yield new Delayed(0);
            $data->data[] = 6;
            yield new Delayed(0);
        };

        $app = $this->callbackApp($executeAppCb);

        $engine = $this->getEngine();
        $engine->onEngineShutdown($cleanupCb);

        $engine->run($app);

        $this->assertSame([1,2,3,4,5,6], $data->data);
    }

    public function testApplicationHandlerInvokedIfApplicationExecuteThrowsException() {
        $engine = $this->getEngine();
        $data = new \stdClass();
        $data->exception = null;

        $throwExceptionCb = function() {
            throw new Exception('Exception thrown in app');
        };

        $handler = function(\Throwable $error) use($data) {
            $data->exception = $error;
        };

        $app = $this->exceptionHandlerApp($throwExceptionCb, $handler);

        $engine->run($app);

        /** @var $event Event */
        $exception = $data->exception;

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame('Exception thrown in app', $exception->getMessage());
    }

    public function eventEmitterProxyData() {
        return [
            ['onEngineBootup', Engine::START_UP_EVENT],
            ['onEngineShutdown', Engine::SHUT_DOWN_EVENT]
        ];
    }

    /**
     * @dataProvider eventEmitterProxyData
     */
    public function testProxyToEventEmitter($method, $event) {
        $cb = function() {
        };
        $engine = $this->getEngine($this->emitter);
        $engine->$method($cb);

        $this->assertSame(1, $this->emitter->listenerCount($event));
        // the empty array is listener data
        $this->assertSame($cb, array_values($this->emitter->listeners($event))[0][0]);
    }

    public function testAppCleanupEventHasCorrectTarget() {
        $data = new \stdClass();
        $data->data = null;
        $this->emitter->on(Engine::SHUT_DOWN_EVENT, function(Event $event) use($data) {
            $data->data = $event->target();
        });

        $engine = $this->getEngine();
        $engine->run($app = $this->noopApp());

        $this->assertSame($app, $data->data);
    }

    public function testCallingRunMultipleTimesThrowsException() {
        $data = new \stdClass();
        $data->data = null;

        $engine = $this->getEngine();
        $handlerCb = function(\Throwable $throwable) use($data) {
            $data->data = $throwable;
        };
        $appCb = function() use($engine) {
            $engine->run($this->noopApp());
            return new Success();
        };
        $app = $this->exceptionHandlerApp($appCb, $handlerCb);
        $engine->run($app);
        $this->assertInstanceOf(InvalidStateException::class, $data->data);
        $this->assertSame(Engine::class . '::run MUST NOT be called while already running.', $data->data->getMessage());
        $this->assertSame(Exceptions::ENGINE_ERR_MULTIPLE_RUN_CALLS, $data->data->getCode());
    }

    public function testEngineStateBeforeRunIsIdle() {
        $engine = $this->getEngine();
        $this->assertSame(Engine::IDLE_STATE, $engine->getState());
    }

    public function testEngineStateDuringRunIsRunning() {
        $engine = $this->getEngine();
        $data = new \stdClass();
        $app = $this->callbackApp(function() use($engine, $data) {
            $data->state = $engine->getState();
        });

        $engine->run($app);

        $this->assertSame(Engine::RUNNING_STATE, $data->state);
    }

    public function testEngineStateAfterRunIsIdle() {
        $engine = $this->getEngine();
        $app = new NoopApplication();
        $engine->run($app);

        $this->assertSame(Engine::IDLE_STATE, $engine->getState());
    }

    public function testEngineStateAfterExceptionIsCrashed() {
        $app = $this->callbackApp(
            function() { throw new RuntimeException('foobar', 42);
            },
            function($err) {
            }
        );
        $engine = $this->getEngine();

        $engine->run($app);

        $this->assertSame(Engine::CRASHED_STATE, $engine->getState());
    }

    public function testEngineBootupEventCalledOnceOnMultipleRunCalls() {
        $data = new \stdClass();
        $data->data = [];
        $this->emitter->on(Engine::START_UP_EVENT, function() use($data) {
            $data->data[] = 1;
        });

        $engine = $this->getEngine();
        $engine->run($this->noopApp());
        $engine->run($this->noopApp());

        $this->assertSame([1], $data->data);
    }

    public function testGettingEmitterIsInstancePassedToConstructor() {
        $actual = $this->getEngine()->getEmitter();
        $this->assertSame($this->emitter, $actual);
    }

    public function testApplicationLoadPluginsCalled() {
        $pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $app = new LoadPluginCalledApplication($pluggable, function() {
        });

        $this->getEngine()->run($app);

        $expected = ['load', 'execute'];

        $this->assertSame($expected, $app->callOrder(), 'Expected the Application::loadPlugins to be called before Application::execute');
    }

    public function testLogMessagesOnSuccessfulApplicationRunNoPlugins() {
        $app = new NoopApplication();
        $engine = $this->getEngine();
        $engine->run($app);

        $expectedRecords = [
            [
                'level' => 'info',
                'message' => 'Starting Plugin loading process.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Completed Plugin loading process.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting Application process.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Completed Application process.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting Application cleanup process.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Completed Application cleanup process. Engine shutting down.',
                'context' => []
            ]
        ];

        $this->assertSame($expectedRecords, $this->logger->records);
    }

    public function testLogMessagesOnSuccessfulApplicationRunWithPlugins() {
        $app = $this->exceptionHandlerApp(
            function() { throw new RuntimeException('foobar', 42);
            },
            function($err) {
            }
        );
        $lineNum = __LINE__ - 2;
        $engine = $this->getEngine();

        try {
            $engine->run($app);
        } catch (RuntimeException $runtimeException) {
            $this->assertSame('foobar', $runtimeException->getMessage());
        }

        $expectedRecords = [
            [
                'level' => 'info',
                'message' => 'Starting Plugin loading process.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Completed Plugin loading process.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting Application process.',
                'context' => []
            ],
            [
                'level' => 'alert',
                'message' => 'The Application threw an exception: ' . RuntimeException::class . ' "foobar"',
                'context' => ['file' => __FILE__, 'line' => $lineNum]
            ],
            [
                'level' => 'info',
                'message' => 'Starting Application cleanup process.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Completed Application cleanup process. Engine shutting down.',
                'context' => []
            ]
        ];

        $this->assertSame($expectedRecords, $this->logger->records);
    }
}
