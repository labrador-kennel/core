<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;


use Labrador\CoreEngine;
use Labrador\Event\AppExecuteEvent;
use Labrador\Event\ExceptionThrownEvent;
use Labrador\Event\PluginBootEvent;
use Labrador\Event\PluginCleanupEvent;
use Labrador\Exception\Exception;
use Labrador\Plugin\PluginManager;
use Labrador\Stub\BootCalledPlugin;
use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;
use Auryn\Injector;
use PHPUnit_Framework_TestCase as UnitTestCase;

class CoreEngineTest extends UnitTestCase {

    private $mockEventDispatcher;
    private $mockPluginManager;

    public function setUp() {
        $this->mockEventDispatcher = $this->getMock(EventEmitterInterface::class);
        $this->mockPluginManager = $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock();
    }

    private function getEngine(EventEmitterInterface $eventEmitter = null, PluginManager $pluginManager = null) {
        $emitter = $eventEmitter ?: $this->mockEventDispatcher;
        $manager = $pluginManager ?: $this->mockPluginManager;
        return new CoreEngine($emitter, $manager);
    }

    public function normalProcessingEventDataProvider() {
        return [
            [0, CoreEngine::PLUGIN_BOOT_EVENT, PluginBootEvent::class],
            [1, CoreEngine::APP_EXECUTE_EVENT, AppExecuteEvent::class],
            [2, CoreEngine::PLUGIN_CLEANUP_EVENT, PluginCleanupEvent::class]
        ];
    }

    /**
     * @dataProvider normalProcessingEventDataProvider
     */
    public function testEventNormalProcessing($dispatchIndex, $eventName, $eventType) {
        $this->mockEventDispatcher->expects($this->at($dispatchIndex))
                                  ->method('emit')
                                  ->with(
                                      $eventName,
                                      $this->callback(function($arg) use($eventType) {
                                          return $arg[0] instanceof $eventType;
                                      })
                                  );

        $engine = $this->getEngine();
        $engine->run();
    }

    public function testExceptionThrownEventDispatched() {
        $this->mockEventDispatcher->expects($this->at(0))
                                  ->method('emit')
                                  ->willThrowException($exception = new Exception());

        $this->mockEventDispatcher->expects($this->at(1))
                                  ->method('emit')
                                  ->with(
                                      CoreEngine::EXCEPTION_THROWN_EVENT,
                                      $this->callback(function($arg) use($exception) {
                                         if ($arg[0] instanceof ExceptionThrownEvent) {
                                             return $arg[0]->getException() === $exception;
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

        # Remember method invocation 1 is gonna be the exception event
        $this->mockEventDispatcher->expects($this->at(2))
                                  ->method('emit')
                                  ->with(
                                      CoreEngine::PLUGIN_CLEANUP_EVENT,
                                      $this->callback(function($arg) {
                                          return $arg[0] instanceof PluginCleanupEvent;
                                      })
                                  );

        $engine = $this->getEngine();
        $engine->run();
    }

    public function testRegisteredPluginsGetBooted() {
        $emitter = new EventEmitter();
        $pluginManager = new PluginManager($this->getMock(Injector::class), $emitter);
        $engine = $this->getEngine($emitter, $pluginManager);

        $plugin = new BootCalledPlugin('boot_called_plugin');
        $engine->registerPlugin($plugin);

        $engine->run();

        $this->assertTrue($plugin->bootCalled(), 'The Plugin::boot method was not called');
    }

} 