<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

use Labrador\Engine;
use Labrador\Event\PluginBootEvent;
use Labrador\Exception\NotFoundException;
use Labrador\Stub\BootCalledPlugin;
use Labrador\Stub\NameOnlyPlugin;
use Labrador\Exception\InvalidArgumentException;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitter;
use Auryn\Injector;
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

    public function testRegisterPluginHasIt() {
        $plugin = new NameOnlyPlugin('foo_plugin');
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertTrue($manager->hasPlugin('foo_plugin'));
    }

    public function testUnregisteredPluginDoesNotHaveIt() {
        $manager = $this->getPluginManager();
        $this->assertFalse($manager->hasPlugin('anything'));
    }

    public function testGettingRegisteredPlugin() {
        $plugin = new NameOnlyPlugin('foo_plugin');
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertSame($plugin, $manager->getPlugin('foo_plugin'));
    }

    public function testGettingUnregisteredPluginThrowsException() {
        $manager = $this->getPluginManager();
        $msg = 'Could not find a registered plugin named "foo"';
        $this->setExpectedException(NotFoundException::class, $msg);
        $manager->getPlugin('foo');
    }

    public function testRemovingRegisteredPlugin() {
        $plugin = new NameOnlyPlugin('foo_plugin');
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertTrue($manager->hasPlugin('foo_plugin'));

        $manager->removePlugin('foo_plugin');
        $this->assertFalse($manager->hasPlugin('foo_plugin'));
    }

    public function testRegisteringEventAwarePluginRegistersListeners() {
        $plugin = $this->getMock(EventAwarePlugin::class);
        $plugin->expects($this->once())->method('registerEventListeners')->with($this->mockDispatcher);

        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
    }

    public function testRegisteringServiceAwarePluginRegistersServices() {
        $plugin = $this->getMock(ServiceAwarePlugin::class);
        $plugin->expects($this->once())->method('registerServices')->with($this->mockInjector);

        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
    }

    public function testPluginsWithInvalidNameThrowsException() {
        $plugin = new NameOnlyPlugin('an inalid name because of the spaces');
        $manager = $this->getPluginManager();
        $msg = 'A valid plugin name may only contain letters, numbers, periods, underscores, and dashes [A-Za-z0-9\.\-\_]';
        $this->setExpectedException(InvalidArgumentException::class, $msg);
        $manager->registerPlugin($plugin);
    }

    public function testGettingCopyOfPlugins() {
        $manager = $this->getPluginManager();
        $copy = $manager->getPlugins();
        $stub = new NameOnlyPlugin('stub');
        $copy['stub'] = $stub;

        $this->assertFalse($manager->hasPlugin('stub'));
    }

    public function testPluginBooterListenerAddedToEventDispatcher() {
        $eventDispatcher = new EventEmitter();
        $manager = new PluginManager($this->mockInjector, $eventDispatcher);
        $manager->registerPlugin($plugin = new BootCalledPlugin('foo'));
        $manager->registerBooter();

        $engine = $this->getMockBuilder(Engine::class)->disableOriginalConstructor()->getMock();

        $eventDispatcher->emit(Engine::PLUGIN_BOOT_EVENT, [new PluginBootEvent($engine)]);
        $this->assertTrue($plugin->bootCalled());
    }

}
