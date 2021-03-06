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
use Cspray\Labrador\EngineState;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Test\Stub\LoadPluginCalledApplication;
use Cspray\Labrador\Test\Stub\NoopApplication;
use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\Test\Stub\TestApplication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as UnitTestCase;
use Psr\Log\Test\TestLogger;
use RuntimeException;
use stdClass;
use Throwable;

class AmpEngineTest extends UnitTestCase {

    /**
     * @var EventEmitter
     */
    private $emitter;

    /**
     * @var TestLogger
     */
    private $logger;

    public function setUp() : void {
        $this->emitter = new AmpEventEmitter();
        $this->logger = new TestLogger();
    }

    private function getEngine(EventEmitter $eventEmitter = null) : AmpEngine {
        $emitter = $eventEmitter ?: $this->emitter;
        $engine = new AmpEngine($emitter);
        $engine->setLogger($this->logger);
        return $engine;
    }

    private function mockPluggable() : Pluggable {
        /** @var MockObject|Pluggable $pluggable */
        $pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $pluggable->expects($this->once())->method('loadPlugins')->willReturn(new Success());
        return $pluggable;
    }

    private function noopApp() : Application {
        $app = new NoopApplication($this->mockPluggable());
        $app->setLogger($this->logger);
        return $app;
    }

    private function stubApp(callable $callback) : Application {
        $app = new TestApplication($this->mockPluggable(), $callback);
        $app->setLogger($this->logger);
        return $app;
    }

    private function exceptionHandlerApp(
        callable $appCallback,
        callable $handler
    ) : Application {
        $app = new TestApplication($this->mockPluggable(), $appCallback, $handler);
        $app->setLogger($this->logger);
        return $app;
    }

    public function testEventsExecutedInOrder() {
        $data = new stdClass();
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

        $engine->run($this->noopApp());

        $this->assertSame([1,2,3,4,5,6], $data->data);
    }

    public function testExecuteAppFinishesBeforeAppCleanup() {
        $data = new stdClass();
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

        $app = $this->stubApp($executeAppCb);

        $engine = $this->getEngine();
        $engine->onEngineShutdown($cleanupCb);

        $engine->run($app);

        $this->assertSame([1,2,3,4,5,6], $data->data);
    }

    public function testApplicationHandlerInvokedIfApplicationExecuteThrowsException() {
        $engine = $this->getEngine();
        $data = new stdClass();
        $data->exception = null;

        $throwExceptionCb = function() {
            throw new Exception('Exception thrown in app');
        };

        $handler = function(Throwable $error) use($data) {
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
        $data = new stdClass();
        $data->data = null;
        $this->emitter->on(Engine::SHUT_DOWN_EVENT, function(Event $event) use($data) {
            $data->data = $event->getTarget();
        });

        $engine = $this->getEngine();
        $engine->run($app = $this->noopApp());

        $this->assertSame($app, $data->data);
    }

    public function testCallingRunMultipleTimesThrowsException() {
        $data = new stdClass();
        $data->data = null;

        $engine = $this->getEngine();
        $handlerCb = function(Throwable $throwable) use($data) {
            $data->data = $throwable;
        };
        $appCb = function() use($engine) {
            $engine->run(new NoopApplication($this->createMock(Pluggable::class)));
            return new Success();
        };
        $app = $this->exceptionHandlerApp($appCb, $handlerCb);
        $engine->run($app);
        $this->assertInstanceOf(InvalidStateException::class, $data->data);
        $this->assertSame(
            Engine::class . '::run MUST NOT be called while already running.',
            $data->data->getMessage()
        );
        $this->assertSame(Exceptions::ENGINE_ERR_MULTIPLE_RUN_CALLS, $data->data->getCode());
    }

    public function testEngineStateBeforeRunIsIdle() {
        $engine = $this->getEngine();
        $this->assertSame(EngineState::Idle(), $engine->getState());
    }

    public function testEngineStateDuringRunIsRunning() {
        $engine = $this->getEngine();
        $data = new stdClass();
        $app = $this->stubApp(function() use($engine, $data) {
            $data->state = $engine->getState();
        });

        $engine->run($app);

        $this->assertSame(EngineState::Running(), $data->state);
    }

    public function testEngineStateAfterRunIsIdle() {
        $engine = $this->getEngine();
        $app = $this->noopApp();
        $engine->run($app);

        $this->assertSame(EngineState::Idle(), $engine->getState());
    }

    public function testEngineStateAfterExceptionIsCrashed() {
        $app = $this->stubApp(
            function() {
                throw new RuntimeException('foobar', 42);
            }
        );
        $engine = $this->getEngine();

        $engine->run($app);

        $this->assertSame(EngineState::Crashed(), $engine->getState());
    }

    public function testEngineBootupEventCalledOnceOnMultipleRunCalls() {
        $data = new stdClass();
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

    public function testApplicationLoadPluginsCalledIfSomeAreRegistered() {
        $pluggable = $this->createMock(Pluggable::class);
        $app = new LoadPluginCalledApplication($pluggable);

        $this->getEngine()->run($app);

        $expected = ['load', 'doStart'];

        $this->assertSame(
            $expected,
            $app->callOrder(),
            'Expected the Application::loadPlugins to be called before Application::start'
        );
    }

    public function testApplicationHandleExceptionThrowsException() {
        $application = $this->getMockBuilder(Application::class)->getMock();
        $application->expects($this->once())
            ->method('loadPlugins')
            ->willReturn(new Success());
        $application->expects($this->once())
            ->method('start')
            ->willThrowException($exception = new RuntimeException());
        $application->expects($this->once())
            ->method('handleException')
            ->with($exception)
            ->willThrowException(new RuntimeException('Throw from handleException'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Throw from handleException');

        $this->getEngine()->run($application);
    }

    public function testShutdownEventListenerThrowsException() {
        $eventEmitter = new AmpEventEmitter();
        $eventEmitter->on(Engine::SHUT_DOWN_EVENT, function() {
            throw new RuntimeException('Thrown from shutdown event.');
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Thrown from shutdown event.');

        $this->getEngine($eventEmitter)->run($this->noopApp());
    }

    public function testLogMessagesOnSuccessfulApplicationRunNoPlugins() {
        $app = $this->noopApp();
        $engine = $this->getEngine();
        $engine->run($app);

        $expectedRecords = [
            [
                'level' => 'info',
                'message' => 'Starting Plugin loading process.',
                'context' => []
            ],

            // We don't expect logging output from the PluginManager here because all of our test apps are using
            // mock pluggables

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
        $app = $this->noopApp();
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
}
