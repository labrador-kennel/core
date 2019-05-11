<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test;

use Amp\PHPUnit\AsyncTestCase;
use Cspray\Labrador\Engine;
use Cspray\Labrador\Exception\InvalidStateException;
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
use Cspray\Labrador\Test\Stub\FooService;
use Cspray\Labrador\Test\Stub\FooServiceBootablePlugin;
use Cspray\Labrador\Test\Stub\GeneratorBooterPlugin;
use Cspray\Labrador\Test\Stub\PluginStub;
use Cspray\Labrador\Test\Stub\RecursivelyDependentPluginStub;
use Cspray\Labrador\Test\Stub\RequiresCircularDependentStub;
use Cspray\Labrador\Test\Stub\RequiresNotPresentPlugin;
use Cspray\Labrador\Test\Stub\ServicesRegisteredPlugin;
use Cspray\Labrador\AsyncEvent\AmpEmitter as EventEmitter;
use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Auryn\Injector;

class PluginManagerTest extends AsyncTestCase {

    private $emitter;
    private $injector;

    public function setUp() {
        parent::setUp();
        $this->emitter = new EventEmitter();
        $this->injector = new Injector();
    }

    private function getPluginManager() {
        return new PluginManager($this->injector, $this->emitter);
    }

    private function standardEvent(string $name, $target, array $eventData = []) : Event {
        return new StandardEvent($name, $target, $eventData);
    }

    private function getMockEngine() {
        return $this->getMockBuilder(Engine::class)->disableOriginalConstructor()->getMock();
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
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);

        yield $manager->loadPlugins();

        $this->assertTrue($plugin->wasCalled());
    }

    public function testSingleDependsOnProcessed() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin($plugin = new FooPluginDependentStub($this->injector));
        $manager->registerPlugin(new FooPluginStub());

        yield $manager->loadPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    public function testDependsOnLoadedOnce() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin(new FooPluginDependentStub($this->injector));
        $manager->registerPlugin($plugin = new FooPluginStub());

        yield $manager->loadPlugins();

        $this->assertSame(1, $plugin->getNumberTimesBootCalled());
    }

    public function testDependentPluginDependsOnDependentPlugin() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin($plugin = new RecursivelyDependentPluginStub($this->injector));
        $manager->registerPlugin(new FooPluginDependentStub($this->injector));
        $manager->registerPlugin(new FooPluginStub());

        yield $manager->loadPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    public function testHandlingCircularDependency() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin(new CircularDependencyPluginStub());
        $manager->registerPlugin(new RequiresCircularDependentStub());

        $exc = CircularDependencyException::class;
        $msg = 'A circular dependency was found with Cspray\\Labrador\\Test\\Stub\\RequiresCircularDependentStub';
        $msg .= ' requiring Cspray\\Labrador\\Test\\Stub\\CircularDependencyPluginStub.';

        $this->expectException($exc);
        $this->expectExceptionMessage($msg);

        yield $manager->loadPlugins();
    }

    public function testDependentPluginNotPresentThrowsException() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin(new RequiresNotPresentPlugin());

        $exc = PluginDependencyNotProvidedException::class;
        $msg = 'Cspray\\Labrador\\Test\\Stub\\RequiresNotPresentPlugin requires a plugin that is not registered:';
        $msg .= ' SomeAwesomePlugin.';

        $this->expectException($exc);
        $this->expectExceptionMessage($msg);

        yield $manager->loadPlugins();
    }

    public function testExceptionThrownIfPluginRegisteredAfterLoading() {
        $manager = $this->getPluginManager();

        yield $manager->loadPlugins();

        $plugin = new BootCalledPlugin();

        $this->expectException(InvalidStateException::class);
        $msg = "Plugins have already been loaded and you MUST NOT register plugins after this has taken place.";
        $this->expectExceptionMessage($msg);

        $manager->registerPlugin($plugin);
    }

    public function testRegisteringSamePluginThrowsException() {
        $manager = $this->getPluginManager();

        $plugin = new FooPluginStub();
        $manager->registerPlugin($plugin);

        $this->expectException(InvalidArgumentException::class);
        $msg = 'A Plugin with name ' . FooPluginStub::class . ' has already been registered and';
        $msg .= ' may not be registered again.';
        $this->expectExceptionMessage($msg);

        $manager->registerPlugin($plugin);
    }

    public function testRegisterCustomPluginHandler() {
        $manager = $this->getPluginManager();

        $plugin = new CustomPluginStub();
        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPlugin($plugin);

        yield $manager->loadPlugins();

        $this->assertSame(1, $plugin->getTimesCalled());
    }

    public function testRegisteringMultipleCustomPluginHandlers() {
        $manager = $this->getPluginManager();

        $plugin = new CustomPluginStub();
        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPlugin($plugin);

        yield $manager->loadPlugins();

        $this->assertSame(2, $plugin->getTimesCalled());
    }

    public function testCustomHandlerInvokedAfterSystemHandlers() {
        $manager = $this->getPluginManager();

        $plugin = new CustomPluginOrderStub();
        $manager->registerPluginHandler(
            CustomPluginOrderStub::class,
            function(CustomPluginOrderStub $pluginStub) {
                $pluginStub->customOp();
            }
        );
        $manager->registerPlugin($plugin);

        yield $manager->loadPlugins();

        $expected = ['depends', 'services', 'events', 'custom', 'boot'];
        $this->assertSame($expected, $plugin->getCallOrder());
    }

    public function testCustomHandlerPassedArgumentsAfterPlugin() {
        $manager = $this->getPluginManager();

        $handlerArgs = new \stdClass();
        $handlerArgs->data = null;
        $plugin = new CustomPluginStub();
        $handler = function(CustomPluginStub $pluginStub, ...$arguments) use($handlerArgs) {
            $handlerArgs->data = $arguments;
        };
        $manager->registerPluginHandler(CustomPluginStub::class, $handler, 'a', 'b', 'c');
        $manager->registerPlugin($plugin);

        yield $manager->loadPlugins();

        $this->assertSame(['a', 'b', 'c'], $handlerArgs->data);
    }

    public function testCustomHandlerHandlesPluginTypesThatAreInterfaces() {
        $manager = $this->getPluginManager();

        $handlerArgs = new \stdClass();
        $handlerArgs->data = null;
        $manager->registerPluginHandler(
            CustomPluginInterface::class,
            function(CustomPluginInterface $plugin) use($handlerArgs) {
                $handlerArgs->data = $plugin;
            }
        );
        $plugin = $this->createMock(CustomPluginInterface::class);
        $manager->registerPlugin($plugin);

        yield $manager->loadPlugins();

        $this->assertSame($plugin, $handlerArgs->data);
    }

    public function testPluginBootMethodRunsOnEventLoop() {
        $manager = $this->getPluginManager();

        $plugin = new GeneratorBooterPlugin();
        $manager->registerPlugin($plugin);

        yield $manager->loadPlugins();

        $this->assertSame(3, $plugin->getTimesYielded());
    }

    public function testPluginBootMethodInvokedByInjector() {
        $manager = $this->getPluginManager();

        $fooService = new FooService();
        $plugin = new FooServiceBootablePlugin($fooService);

        $manager->registerPlugin($plugin);

        yield $manager->loadPlugins();

        $bootedService = $plugin->getBootInjectedService();

        $this->assertSame($fooService, $bootedService);
    }
}
