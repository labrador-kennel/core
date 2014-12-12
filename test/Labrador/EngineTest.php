<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Labrador\Engine;
use Labrador\Event\AppExecuteEvent;
use Labrador\Event\ExceptionThrownEvent;
use Labrador\Event\PluginBootEvent;
use Labrador\Event\PluginCleanupEvent;
use Labrador\Events;
use Labrador\Exception\Exception;
use Labrador\Plugin\PluginManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PHPUnit_Framework_TestCase as UnitTestCase;

class EngineTest extends UnitTestCase {

    private $mockEventDispatcher;
    private $mockPluginManager;

    public function setUp() {
        $this->mockEventDispatcher = $this->getMock(EventDispatcherInterface::class);
        $this->mockPluginManager = $this->getMockBuilder(PluginManager::class)->disableOriginalConstructor()->getMock();
    }

    private function getEngine() {
        return new Engine($this->mockEventDispatcher, $this->mockPluginManager);
    }

    public function normalProcessingEventDataProvider() {
        return [
            [0, Engine::PLUGIN_BOOT_EVENT, PluginBootEvent::class],
            [1, Engine::APP_EXECUTE_EVENT, AppExecuteEvent::class],
            [2, Engine::PLUGIN_CLEANUP_EVENT, PluginCleanupEvent::class]
        ];
    }

    /**
     * @dataProvider normalProcessingEventDataProvider
     */
    public function testEventNormalProcessing($dispatchIndex, $eventName, $eventType) {
        $this->mockEventDispatcher->expects($this->at($dispatchIndex))
                                  ->method('dispatch')
                                  ->with(
                                      $eventName,
                                      $this->callback(function($arg) use($eventType) {
                                          return $arg instanceof $eventType;
                                      })
                                  );

        $engine = $this->getEngine();
        $engine->run();
    }

    public function testExceptionThrownEventDispatched() {
        $this->mockEventDispatcher->expects($this->at(0))
                                  ->method('dispatch')
                                  ->willThrowException($exception = new Exception());

        $this->mockEventDispatcher->expects($this->at(1))
                                  ->method('dispatch')
                                  ->with(
                                      Engine::EXCEPTION_THROWN_EVENT,
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
                                  ->method('dispatch')
                                  ->willThrowException($exception = new Exception());

        # Remember method invocation 1 is gonna be the exception event
        $this->mockEventDispatcher->expects($this->at(2))
                                  ->method('dispatch')
                                  ->with(
                                      Engine::PLUGIN_CLEANUP_EVENT,
                                      $this->callback(function($arg) {
                                          return $arg instanceof PluginCleanupEvent;
                                      })
                                  );

        $engine = $this->getEngine();
        $engine->run();
    }

} 
