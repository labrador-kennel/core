<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test;

use Amp\Loop;
use Cspray\Labrador\Engine;
use Cspray\Labrador\PluginManager;
use Cspray\Labrador\Plugin\Plugin;

use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\PluginDependencyNotProvidedException;

use Cspray\Labrador\Test\Stub\BootCalledPlugin;
use Cspray\Labrador\Test\Stub\CircularDependencyPluginStub;
use Cspray\Labrador\Test\Stub\CustomPluginInterface;
use Cspray\Labrador\Test\Stub\CustomPluginOrderStub;
use Cspray\Labrador\Test\Stub\CustomPluginStub;
use Cspray\Labrador\Test\Stub\EventsRegisteredPlugin;
use Cspray\Labrador\Test\Stub\FooPluginDependentStub;
use Cspray\Labrador\Test\Stub\FooPluginStub;
use Cspray\Labrador\Test\Stub\PluginStub;
use Cspray\Labrador\Test\Stub\RecursivelyDependentPluginStub;
use Cspray\Labrador\Test\Stub\RequiresCircularDependentStub;
use Cspray\Labrador\Test\Stub\RequiresNotPresentPlugin;
use Cspray\Labrador\Test\Stub\ServicesRegisteredPlugin;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\AmpEmitter as EventEmitter;
use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Auryn\Injector;
use PHPUnit\Framework\TestCase as UnitTestCase;

class PluginManagerTest extends UnitTestCase {

    private $mockDispatcher;
    private $mockInjector;

    public function setUp() {
        $this->mockDispatcher = $this->createMock(Emitter::class);
        $this->mockInjector = $this->getMockBuilder(Injector::class)->disableOriginalConstructor()->getMock();
    }

    private function getPluginManager() {
        return new PluginManager($this->mockInjector, $this->mockDispatcher);
    }

    private function getPluginBooter(PluginManager $mgr) {
        $r = new \ReflectionClass($mgr);
        $p = $r->getProperty('booter');
        $p->setAccessible(true);
        return $p->getValue($mgr);
    }

    private function standardEvent(string $name, $target, array $eventData = []) : Event {
        return new StandardEvent($name, $target, $eventData);
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

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf($msg, PluginStub::class));

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

    public function correctPluginMethodsCalledProvider() {
        return [
            [new BootCalledPlugin()],
            [new ServicesRegisteredPlugin()],
            [new EventsRegisteredPlugin()]
        ];
    }

    /**
     * @dataProvider correctPluginMethodsCalledProvider
     */
    public function testPluginMethodsCalled(Plugin $plugin) {
        $emitter = new EventEmitter();
        $manager = new PluginManager($this->mockInjector, $emitter);
        $manager->registerPlugin($plugin);

        $engine = $this->getMockBuilder(Engine::class)->disableOriginalConstructor()->getMock();

        Loop::run(function() use($emitter, $engine) {
            $emitter->emit($this->standardEvent(Engine::ENGINE_BOOTUP_EVENT, $engine));
        });

        $this->assertTrue($plugin->wasCalled());
    }

    public function testSingleDependsOnProcessed() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin($plugin = new FooPluginDependentStub($injector));
        $manager->registerPlugin(new FooPluginStub());

        $booter->bootPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    public function testDependsOnLoadedOnce() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin(new FooPluginDependentStub($injector));
        $manager->registerPlugin($plugin = new FooPluginStub());

        $booter->bootPlugins();

        $this->assertSame(1, $plugin->getNumberTimesBootCalled());
    }

    public function testDependentPluginDependsOnDependentPlugin() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin($plugin = new RecursivelyDependentPluginStub($injector));
        $manager->registerPlugin(new FooPluginDependentStub($injector));
        $manager->registerPlugin(new FooPluginStub());

        $booter->bootPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    public function testHandlingCircularDependency() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin(new CircularDependencyPluginStub());
        $manager->registerPlugin(new RequiresCircularDependentStub());

        $exc = CircularDependencyException::class;
        $msg = "A circular dependency was found with Cspray\\Labrador\\Test\\Stub\\RequiresCircularDependentStub requiring Cspray\\Labrador\\Test\\Stub\\CircularDependencyPluginStub.";

        $this->expectException($exc);
        $this->expectExceptionMessage($msg);

        $booter->bootPlugins();
    }

    public function testDependentPluginNotPresentThrowsException() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin(new RequiresNotPresentPlugin());

        $exc = PluginDependencyNotProvidedException::class;
        $msg = 'Cspray\\Labrador\\Test\\Stub\\RequiresNotPresentPlugin requires a plugin that is not registered: SomeAwesomePlugin.';

        $this->expectException($exc);
        $this->expectExceptionMessage($msg);

        $booter->bootPlugins();
    }

    public function testLoadPluginIfRegisteredAfterPluginBootEvent() {
        $emitter = new EventEmitter();
        $manager = new PluginManager($this->mockInjector, $emitter);

        Loop::run(function() use($emitter) {
            $emitter->emit($this->standardEvent(Engine::ENGINE_BOOTUP_EVENT, new \stdClass()));
        });

        $plugin = new BootCalledPlugin();

        $manager->registerPlugin($plugin);

        $this->assertTrue($plugin->wasCalled());
    }

    public function testRegisteringSamePluginThrowsException() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);

        $plugin = new FooPluginStub();
        $manager->registerPlugin($plugin);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A Plugin with name ' . FooPluginStub::class . ' has already been registered and may not be registered again.');

        $manager->registerPlugin($plugin);
    }

    public function testRegisterCustomPluginHandler() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);

        $plugin = new CustomPluginStub();
        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPlugin($plugin);

        $booter = $this->getPluginBooter($manager);
        $booter->bootPlugins();

        $this->assertSame(1, $plugin->getTimesCalled(), 'Expected method from custom plugin handler to have been invoked');
    }

    public function testRegisteringMultipleCustomPluginHandlers() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);

        $plugin = new CustomPluginStub();
        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPlugin($plugin);

        $booter = $this->getPluginBooter($manager);
        $booter->bootPlugins();

        $this->assertSame(2, $plugin->getTimesCalled(), 'Expected method from custom plugin handler to have been invoked');
    }

    public function testCustomHandlerInvokedAfterSystemHandlers() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);

        $plugin = new CustomPluginOrderStub();
        $manager->registerPluginHandler(CustomPluginOrderStub::class, function(CustomPluginOrderStub $pluginStub) {
            $pluginStub->customOp();
        });
        $manager->registerPlugin($plugin);

        $booter = $this->getPluginBooter($manager);
        $booter->bootPlugins();

        $expected = ['depends', 'services', 'events', 'custom', 'boot'];
        $this->assertSame($expected, $plugin->getCallOrder(), 'Expected plugin handlers to be invoked in a specific order and they were not');
    }

    public function testCustomHandlerPassedArgumentsAfterPlugin() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);

        $handlerArgs = new \stdClass();
        $handlerArgs->data = null;
        $plugin = new CustomPluginStub();
        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub, ...$arguments) use($handlerArgs) {
            $handlerArgs->data = $arguments;
        }, 'a', 'b', 'c');
        $manager->registerPlugin($plugin);

        $booter = $this->getPluginBooter($manager);
        $booter->bootPlugins();

        $this->assertSame(['a', 'b', 'c'], $handlerArgs->data, 'Expected the arguments passed to handler registration to be passed to handler');
    }

    public function testCustomHandlerHandlesPluginTypesThatAreInterfaces() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);

        $handlerArgs = new \stdClass();
        $handlerArgs->data = null;
        $manager->registerPluginHandler(CustomPluginInterface::class, function(CustomPluginInterface $plugin) use($handlerArgs) {
            $handlerArgs->data = $plugin;
        });
        $plugin = $this->createMock(CustomPluginInterface::class);
        $manager->registerPlugin($plugin);

        $booter = $this->getPluginBooter($manager);
        $booter->bootPlugins();

        $this->assertSame($plugin, $handlerArgs->data);
    }
}
