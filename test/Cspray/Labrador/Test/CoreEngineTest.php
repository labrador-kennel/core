<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test;

use Cspray\Labrador\CoreEngine;
use Cspray\Labrador\Engine;
use Cspray\Labrador\PluginManager;
use Cspray\Labrador\Event\AppExecuteEvent;
use Cspray\Labrador\Event\ExceptionThrownEvent;
use Cspray\Labrador\Event\AppCleanupEvent;
use Cspray\Labrador\Event\EnvironmentInitializeEvent;
use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Test\Stub\BootCalledPlugin;
use Cspray\Labrador\Test\Stub\PluginStub;
use Auryn\Injector;
use League\Event\Emitter as EventEmitter;
use League\Event\EmitterInterface;
use Cspray\Telluris\Environment;
use PHPUnit_Framework_TestCase as UnitTestCase;

class CoreEngineTest extends UnitTestCase {

    private $mockEnvironment;
    private $mockEventDispatcher;
    private $mockPluginManager;

    public function setUp() {
        $this->mockEnvironment = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $this->mockEventDispatcher = $this->getMock(EmitterInterface::class);
        $this->mockPluginManager = $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock();
    }

    private function getEngine(EmitterInterface $eventEmitter = null, PluginManager $pluginManager = null) {
        $emitter = $eventEmitter ?: $this->mockEventDispatcher;
        $manager = $pluginManager ?: $this->mockPluginManager;
        return new CoreEngine($this->mockEnvironment, $manager, $emitter);
    }

    public function normalProcessingEventDataProvider() {
        return [
            [0, EnvironmentInitializeEvent::class],
            [1, AppExecuteEvent::class],
            [2, AppCleanupEvent::class]
        ];
    }

    /**
     * @dataProvider normalProcessingEventDataProvider
     */
    public function testEventNormalProcessing($dispatchIndex, $eventType) {
        $engine = $this->getEngine();
        $this->mockEventDispatcher->expects($this->at($dispatchIndex))
                                  ->method('emit')
                                  ->with(
                                      $this->callback(function($arg) use($eventType) {
                                          return $arg instanceof $eventType;
                                      }),
                                      $this->callback(function($arg) use($engine) {
                                          return $arg === $engine;
                                      })
                                  );
        $engine->run();
    }

    public function testExceptionThrownEventDispatched() {
        $this->mockEventDispatcher->expects($this->at(0))
                                  ->method('emit')
                                  ->willThrowException($exception = new Exception());

        $this->mockEventDispatcher->expects($this->at(1))
                                  ->method('emit')
                                  ->with(
                                      $this->callback(function($arg) use($exception) {
                                         if ($arg instanceof ExceptionThrownEvent) {
                                             return $arg->getException() === $exception;
                                         }

                                         return false;
                                      })
                                  );

        $engine = $this->getEngine();
        $engine->run();
    }

    public function testPluginCleanupEventDispatchedWhenExceptionCaught() {
        $this->mockEventDispatcher->expects($this->at(0))
                                  ->method('emit')
                                  ->willThrowException($exception = new Exception());
        $engine = $this->getEngine();

        # Remember method invocation 1 is gonna be the exception event
        $this->mockEventDispatcher->expects($this->at(2))
                                  ->method('emit')
                                  ->with(
                                      $this->callback(function($arg) {
                                          return $arg instanceof AppCleanupEvent;
                                      }),
                                      $this->callback(function($arg) use($engine) {
                                          return $arg === $engine;
                                      })
                                  );

        $engine->run();
    }

    public function testRegisteredPluginsGetBooted() {
        $emitter = new EventEmitter();
        $pluginManager = new PluginManager($this->getMock(Injector::class), $emitter);
        $engine = $this->getEngine($emitter, $pluginManager);

        $plugin = new BootCalledPlugin('boot_called_plugin');
        $engine->registerPlugin($plugin);

        $engine->run();

        $this->assertTrue($plugin->wasCalled(), 'The Plugin::boot method was not called');
    }

    public function eventEmitterProxyData() {
        return [
            ['onEnvironmentInitialize', Engine::ENVIRONMENT_INITIALIZE_EVENT],
            ['onAppExecute', Engine::APP_EXECUTE_EVENT],
            ['onAppCleanup', Engine::APP_CLEANUP_EVENT],
            ['onExceptionThrown', Engine::EXCEPTION_THROWN_EVENT]
        ];
    }

    /**
     * @dataProvider eventEmitterProxyData
     */
    public function testProxyToEventEmitter($method, $event) {
        $cb = function() {};
        $emitter = $this->getMock(EmitterInterface::class);
        $emitter->expects($this->once())
                ->method('addListener')
                ->with($event, $cb);

        $engine = $this->getEngine($emitter);
        $engine->$method($cb);

        $engine->run();
    }

    public function pluginManagerProxyData() {
        return [
            ['removePlugin', PluginStub::class, null],
            ['hasPlugin', PluginStub::class, true],
            ['getPlugin', PluginStub::class, new PluginStub()],
            ['getPlugins', null, []]
        ];
    }

    /**
     * @dataProvider pluginManagerProxyData
     */
    public function testProxyToPluginManager($method, $arg, $returnVal) {
        $pluginManager = $this->getMockBuilder(PluginManager::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $pluginMethod = $pluginManager->expects($this->once())
                                      ->method($method);
        if ($arg) {
            $pluginMethod->with($arg);
        }

        if (!is_null($returnVal)) {
            $pluginMethod->willReturn($returnVal);
        }

        $this->getEngine(null, $pluginManager)->$method($arg);
    }

}
