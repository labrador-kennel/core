<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test;

use Amp\PHPUnit\AsyncTestCase;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Plugin\PluginManager;
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
use Cspray\Labrador\Test\Stub\FooInjectorBootablePlugin;
use Cspray\Labrador\Test\Stub\GeneratorBooterPlugin;
use Cspray\Labrador\Test\Stub\PluginStub;
use Cspray\Labrador\Test\Stub\RecursivelyDependentPluginStub;
use Cspray\Labrador\Test\Stub\RequiresCircularDependentStub;
use Cspray\Labrador\Test\Stub\RequiresNotPresentPlugin;
use Cspray\Labrador\Test\Stub\ServicesRegisteredPlugin;
use Cspray\Labrador\AsyncEvent\AmpEmitter as EventEmitter;
use Auryn\Injector;
use Auryn\ConfigException;
use stdClass;
use Generator;

class PluginManagerTest extends AsyncTestCase {

    private $emitter;
    /** @var Injector */
    private $injector;

    public function setUp() {
        parent::setUp();
        $this->emitter = new EventEmitter();
        $this->injector = new Injector();
    }

    private function getPluginManager() {
        return new PluginManager($this->injector, $this->emitter);
    }

    /**
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testManagerHasRegisteredPlugin() {
        $manager = $this->getPluginManager();
        $manager->registerPlugin(PluginStub::class);
        $this->assertTrue($manager->hasPlugin(PluginStub::class));
    }

    public function testManagerDoesNotHavePlugin() {
        $manager = $this->getPluginManager();
        $this->assertFalse($manager->hasPlugin(PluginStub::class));
    }

    /**
     * @throws InvalidStateException
     * @throws NotFoundException
     */
    public function testGettingUnregisteredPluginThrowsException() {
        $manager = $this->getPluginManager();
        $msg = 'Could not find a registered plugin named "%s"';

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf($msg, PluginStub::class));

        $manager->getLoadedPlugin(PluginStub::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testRemovingRegisteredPlugin() {
        $manager = $this->getPluginManager();
        $manager->registerPlugin(PluginStub::class);
        $this->assertTrue($manager->hasPlugin(PluginStub::class));

        $manager->removePlugin(PluginStub::class);
        $this->assertFalse($manager->hasPlugin(PluginStub::class));
    }

    public function correctPluginMethodsCalledProvider() {
        return [
            [new BootCalledPlugin(), function(BootCalledPlugin $plugin) { return $plugin->wasCalled(); }],
            [new ServicesRegisteredPlugin(), function(ServicesRegisteredPlugin $plugin) {
                return $plugin->wasCalled();
            }],
            [new EventsRegisteredPlugin(), function(EventsRegisteredPlugin $plugin) { return $plugin->wasCalled(); }]
        ];
    }

    /**
     * @dataProvider correctPluginMethodsCalledProvider
     *
     * @param Plugin $plugin
     * @param callable $getWasCalled
     * @return Generator
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws CircularDependencyException
     * @throws ConfigException
     */
    public function testPluginMethodsCalled(Plugin $plugin, callable $getWasCalled) {
        $this->injector->share($plugin);
        $manager = $this->getPluginManager();
        $manager->registerPlugin(get_class($plugin));

        yield $manager->loadPlugins();

        $this->assertTrue($getWasCalled($plugin));
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testSingleDependsOnProcessed() {
        $plugin = new FooPluginDependentStub($this->injector);
        $this->injector->share($plugin);
        $manager = $this->getPluginManager();

        $manager->registerPlugin(FooPluginDependentStub::class);
        $manager->registerPlugin(FooPluginStub::class);

        yield $manager->loadPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testDependsOnLoadedOnce() {
        $this->injector->share($dependentPlugin = new FooPluginDependentStub($this->injector));
        $this->injector->share($plugin = new FooPluginStub());
        $manager = $this->getPluginManager();

        $manager->registerPlugin(get_class($dependentPlugin));
        $manager->registerPlugin(get_class($plugin));

        yield $manager->loadPlugins();

        $this->assertSame(1, $plugin->getNumberTimesBootCalled());
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testDependentPluginDependsOnDependentPlugin() {
        $this->injector->share($this->injector);
        $this->injector->share($plugin = new RecursivelyDependentPluginStub($this->injector));
        $manager = $this->getPluginManager();

        $manager->registerPlugin(get_class($plugin));
        $manager->registerPlugin(FooPluginDependentStub::class);
        $manager->registerPlugin(FooPluginStub::class);

        yield $manager->loadPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testHandlingCircularDependency() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin(CircularDependencyPluginStub::class);
        $manager->registerPlugin(RequiresCircularDependentStub::class);

        $exc = CircularDependencyException::class;
        $msg = 'A circular dependency was found with Cspray\\Labrador\\Test\\Stub\\RequiresCircularDependentStub';
        $msg .= ' requiring Cspray\\Labrador\\Test\\Stub\\CircularDependencyPluginStub.';

        $this->expectException($exc);
        $this->expectExceptionMessage($msg);

        yield $manager->loadPlugins();
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testDependentPluginNotPresentThrowsException() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin(RequiresNotPresentPlugin::class);

        $exc = PluginDependencyNotProvidedException::class;
        $msg = 'Cspray\\Labrador\\Test\\Stub\\RequiresNotPresentPlugin requires a plugin that is not registered:';
        $msg .= ' SomeAwesomePlugin.';

        $this->expectException($exc);
        $this->expectExceptionMessage($msg);

        yield $manager->loadPlugins();
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testExceptionThrownIfPluginRegisteredAfterLoading() {
        $manager = $this->getPluginManager();

        yield $manager->loadPlugins();

        $this->expectException(InvalidStateException::class);
        $msg = "Plugins have already been loaded and you MUST NOT register plugins after this has taken place.";
        $this->expectExceptionMessage($msg);

        $manager->registerPlugin(BootCalledPlugin::class);
    }

    /**
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testRegisteringSamePluginThrowsException() {
        $this->injector->share($plugin = new FooPluginStub());
        $manager = $this->getPluginManager();

        $manager->registerPlugin(get_class($plugin));

        $this->expectException(InvalidArgumentException::class);
        $msg = 'A Plugin with name ' . FooPluginStub::class . ' has already been registered and';
        $msg .= ' may not be registered again.';
        $this->expectExceptionMessage($msg);

        $manager->registerPlugin(get_class($plugin));
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testRegisterCustomPluginHandler() {
        $this->injector->share($plugin = new CustomPluginStub());
        $manager = $this->getPluginManager();

        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPlugin(get_class($plugin));

        yield $manager->loadPlugins();

        $this->assertSame(1, $plugin->getTimesCalled());
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testRegisteringMultipleCustomPluginHandlers() {
        $this->injector->share($plugin = new CustomPluginStub());
        $manager = $this->getPluginManager();

        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPluginHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPlugin(get_class($plugin));

        yield $manager->loadPlugins();

        $this->assertSame(2, $plugin->getTimesCalled());
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testCustomHandlerInvokedAfterSystemHandlers() {
        $this->injector->share($plugin = new CustomPluginOrderStub());
        $manager = $this->getPluginManager();

        $manager->registerPluginHandler(
            CustomPluginOrderStub::class,
            function(CustomPluginOrderStub $pluginStub) {
                $pluginStub->customOp();
            }
        );
        $manager->registerPlugin(get_class($plugin));

        yield $manager->loadPlugins();

        $expected = ['depends', 'services', 'events', 'custom', 'boot'];
        $this->assertSame($expected, $plugin->getCallOrder());
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testCustomHandlerPassedArgumentsAfterPlugin() {
        $manager = $this->getPluginManager();

        $handlerArgs = new stdClass();
        $handlerArgs->data = null;
        $handler = function(CustomPluginStub $pluginStub, ...$arguments) use($handlerArgs) {
            $handlerArgs->pluginStub = $pluginStub;
            $handlerArgs->data = $arguments;
        };
        $manager->registerPluginHandler(CustomPluginStub::class, $handler, 'a', 'b', 'c');
        $manager->registerPlugin(CustomPluginStub::class);

        yield $manager->loadPlugins();

        $this->assertSame(['a', 'b', 'c'], $handlerArgs->data);
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testCustomHandlerHandlesPluginTypesThatAreInterfaces() {
        $manager = $this->getPluginManager();

        $handlerArgs = new stdClass();
        $handlerArgs->data = null;
        $manager->registerPluginHandler(
            CustomPluginInterface::class,
            function(CustomPluginInterface $plugin) use($handlerArgs) {
                $handlerArgs->data = $plugin;
            }
        );
        $plugin = $this->createMock(CustomPluginInterface::class);
        $this->injector->share($plugin);
        $manager->registerPlugin(get_class($plugin));

        yield $manager->loadPlugins();

        $this->assertSame($plugin, $handlerArgs->data);
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testPluginBootMethodRunsOnEventLoop() {
        $plugin = new GeneratorBooterPlugin();
        $this->injector->share($plugin);
        $manager = $this->getPluginManager();

        $manager->registerPlugin(GeneratorBooterPlugin::class);

        yield $manager->loadPlugins();

        $this->assertSame(3, $plugin->getTimesYielded());
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testPluginBootMethodInvokedByInjector() {
        $manager = $this->getPluginManager();

        $fooService = new FooService();
        $this->injector->share($plugin = new FooInjectorBootablePlugin($fooService));

        $manager->registerPlugin(get_class($plugin));

        yield $manager->loadPlugins();

        $bootedService = $plugin->getBootInjectedService();

        $this->assertSame($fooService, $bootedService);
    }
}
