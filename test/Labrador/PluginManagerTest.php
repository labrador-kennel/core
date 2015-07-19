<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Labrador\Engine;
use Labrador\Event\PluginBootEvent;
use Labrador\Exception\NotFoundException;
use Labrador\Stub\BootCalledPlugin;
use Labrador\Stub\EventsRegisteredPlugin;
use Labrador\Stub\PluginStub;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitter;
use Auryn\Injector;
use Labrador\Stub\ServicesRegisteredPlugin;
use PHPUnit_Framework_TestCase as UnitTestCase;

class PluginManagerTest extends UnitTestCase {

    private $mockDispatcher;
    private $mockInjector;

    public function setUp() {
        $this->mockDispatcher = $this->getMock(EventEmitterInterface::class);
        $this->mockInjector = $this->getMockBuilder(Injector::class)->disableOriginalConstructor()->getMock();
    }

    private function getPluginManager() {
        return new PluginManager($this->mockInjector, $this->mockDispatcher);
    }

    public function testManagerHasRegisteredPlugin() {
        $plugin = new PluginStub();
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertTrue($manager->hasPlugin(PluginStub::class));
    }

    public function testManagerDoesNotHavePlugin() {
        $manager = $this->getPluginManager();
        $this->assertFalse($manager->hasPlugin(PluginStub::class));
    }

    public function testGettingRegisteredPlugin() {
        $plugin = new PluginStub();
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertSame($plugin, $manager->getPlugin(PluginStub::class));
    }

    public function testGettingUnregisteredPluginThrowsException() {
        $manager = $this->getPluginManager();
        $msg = 'Could not find a registered plugin named "%s"';
        $this->setExpectedException(NotFoundException::class, sprintf($msg, PluginStub::class));
        $manager->getPlugin(PluginStub::class);
    }

    public function testRemovingRegisteredPlugin() {
        $plugin = new PluginStub();
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertTrue($manager->hasPlugin(PluginStub::class));

        $manager->removePlugin(PluginStub::class);
        $this->assertFalse($manager->hasPlugin(PluginStub::class));
    }

    public function testGettingCopyOfPlugins() {
        $manager = $this->getPluginManager();
        $copy = $manager->getPlugins();
        $stub = new PluginStub();
        $copy['stub'] = $stub;

        $this->assertFalse($manager->hasPlugin(PluginStub::class));
    }

    public function testPluginBooterListenerAddedToEventDispatcher() {
        $eventDispatcher = new EventEmitter();
        $manager = new PluginManager($this->mockInjector, $eventDispatcher);
        $manager->registerPlugin($plugin = new BootCalledPlugin());

        $engine = $this->getMockBuilder(Engine::class)->disableOriginalConstructor()->getMock();

        $eventDispatcher->emit(Engine::PLUGIN_BOOT_EVENT, [new PluginBootEvent(), $engine]);
        $this->assertTrue($plugin->bootCalled());
    }

    public function testPluginServicesRegisteredOnBoot() {
        $emitter = new EventEmitter();
        $manager = new PluginManager($this->mockInjector, $emitter);
        $manager->registerPlugin($plugin = new ServicesRegisteredPlugin());

        $engine = $this->getMockBuilder(Engine::class)->disableOriginalConstructor()->getMock();

        $emitter->emit(Engine::PLUGIN_BOOT_EVENT, [new PluginBootEvent(), $engine]);
        $this->assertTrue($plugin->wasCalled());
    }

    public function testPluginEventsRegisteredOnBoot() {
        $emitter = new EventEmitter();
        $manager = new PluginManager($this->mockInjector, $emitter);
        $manager->registerPlugin($plugin = new EventsRegisteredPlugin());

        $engine = $this->getMockBuilder(Engine::class)->disableOriginalConstructor()->getMock();

        $emitter->emit(Engine::PLUGIN_BOOT_EVENT, [new PluginBootEvent(), $engine]);
        $this->assertTrue($plugin->wasCalled());
    }


}
