<?php

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
use Cspray\Labrador\Plugin\PluginManager;
use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Test\Stub\CallbackApplication;
use Cspray\Labrador\Test\Stub\CustomPluginStub;
use Cspray\Labrador\Test\Stub\ExceptionHandlerApplication;
use Cspray\Labrador\Test\Stub\NoopApplication;
use Cspray\Labrador\Test\Stub\PluginStub;
use Cspray\Labrador\Test\Stub\BootCalledPlugin;
use Auryn\Injector;
use Cspray\Labrador\AsyncEvent\AmpEmitter;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\AmpEmitter as EventEmitter;
use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\Test\Stub\ExtraEventEmitArgs;
use PHPUnit\Framework\TestCase as UnitTestCase;

class AmpEngineTest extends UnitTestCase {

    /**
     * @var Emitter
     */
    private $emitter;
    /**
     * @var Injector
     */
    private $injector;

    public function setUp() {
        $this->injector = new Injector();
        $this->emitter = new AmpEmitter();
    }

    private function getEngine(Emitter $eventEmitter = null) : AmpEngine {
        $emitter = $eventEmitter ?: $this->emitter;
        return new AmpEngine($emitter);
    }

    private function noopApp() : Application {
        return new NoopApplication();
    }

    private function callbackApp(callable $callback) : Application {
        return new CallbackApplication($callback);
    }

    private function exceptionHandlerApp(callable $appCallback, callable $handler) : Application {
        return new ExceptionHandlerApplication($appCallback, $handler);
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
            ['onEngineBootup', Engine::ENGINE_BOOTUP_EVENT],
            ['onEngineShutdown', Engine::ENGINE_SHUTDOWN_EVENT]
        ];
    }

    /**
     * @dataProvider eventEmitterProxyData
     */
    public function testProxyToEventEmitter($method, $event) {
        $cb = function() {};
        $engine = $this->getEngine($this->emitter);
        $engine->$method($cb);

        $this->assertSame(1, $this->emitter->listenerCount($event));
        // the empty array is listener data
        $this->assertSame($cb, array_values($this->emitter->listeners($event))[0][0]);
    }

    public function testAppCleanupEventHasCorrectTarget() {
        $data = new \stdClass();
        $data->data = null;
        $this->emitter->on(Engine::ENGINE_SHUTDOWN_EVENT, function(Event $event) use($data) {
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
        $this->assertSame('Engine::run() MUST NOT be called while already running.', $data->data->getMessage());
    }

    public function testEngineStateAfterRunIsIdle() {
        $engine = $this->getEngine();
        $app = new NoopApplication();
        $engine->run($app);

        $this->assertAttributeSame('idle', 'engineState', $engine);
    }

    public function testEngineBootupEventCalledOnceOnMultipleRunCalls() {
        $data = new \stdClass();
        $data->data = [];
        $this->emitter->on(Engine::ENGINE_BOOTUP_EVENT, function() use($data) {
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

}
