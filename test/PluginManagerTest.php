<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function Amp\async;
use Amp\PHPUnit\AsyncTestCase;
use Cspray\Labrador\Engine;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\PluginManager;
use Cspray\Labrador\Plugin\Plugin;

use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Exception\CircularDependencyException;

use Cspray\Labrador\Test\Stub\BootCalledPlugin;
use Cspray\Labrador\Test\Stub\CircularDependencyPluginStub;
use Cspray\Labrador\Test\Stub\CustomPluginInterface;
use Cspray\Labrador\Test\Stub\CustomPluginOrderStub;
use Cspray\Labrador\Test\Stub\CustomPluginStub;
use Cspray\Labrador\Test\Stub\EventsRegisteredPlugin;
use Cspray\Labrador\Test\Stub\FooPluginDependentStub;
use Cspray\Labrador\Test\Stub\FooPluginStub;
use Cspray\Labrador\Test\Stub\PluginDependsNotPluginStub;
use Cspray\Labrador\Test\Stub\PluginStub;
use Cspray\Labrador\Test\Stub\RecursivelyDependentPluginStub;
use Cspray\Labrador\Test\Stub\RequiresCircularDependentStub;
use Cspray\Labrador\Test\Stub\ServicesRegisteredPlugin;
use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Auryn\Injector;
use Auryn\ConfigException;
use stdClass;
use Generator;
use function Amp\delay;

class PluginManagerTest extends AsyncTestCase {

    private EventEmitter $emitter;
    private Injector $injector;
    private TestHandler $logHandler;
    private LoggerInterface $logger;

    public function setUp() : void {
        parent::setUp();
        $this->emitter = new AmpEventEmitter();
        $this->injector = new Injector();
        $this->logHandler = new TestHandler();
        $this->logger = new Logger('labrador-core-test', [$this->logHandler]);
    }

    private function getPluginManager() : PluginManager {
        $pluginManager = new PluginManager($this->injector, $this->emitter);
        $pluginManager->setLogger($this->logger);
        return $pluginManager;
    }

    /**
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testManagerHasRegisteredPlugin() {
        $manager = $this->getPluginManager();
        $manager->registerPlugin(PluginStub::class);
        $this->assertTrue($manager->hasPluginBeenRegistered(PluginStub::class));
    }

    public function testManagerDoesNotHavePlugin() {
        $manager = $this->getPluginManager();
        $this->assertFalse($manager->hasPluginBeenRegistered(PluginStub::class));
    }

    /**
     * @throws InvalidStateException
     * @throws NotFoundException
     */
    public function testGettingUnregisteredPluginThrowsException() {
        $manager = $this->getPluginManager();
        $msg = 'Could not find a Plugin named "%s"';

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
        $this->assertTrue($manager->hasPluginBeenRegistered(PluginStub::class));

        $manager->removePlugin(PluginStub::class);
        $this->assertFalse($manager->hasPluginBeenRegistered(PluginStub::class));
    }

    /**
     * @throws CircularDependencyException
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testRemovingLoadedEventAwarePluginCallsRemoveListeners() {
        $plugin = new EventsRegisteredPlugin();
        $this->injector->share($plugin);
        $manager = $this->getPluginManager();
        $manager->registerPlugin(EventsRegisteredPlugin::class);

        $manager->loadPlugins();

        $manager->removePlugin(EventsRegisteredPlugin::class);

        $this->assertTrue($plugin->wasRemoveCalled());
    }

    /**
     * @throws CircularDependencyException
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testRemoveLoadedPluginWthCustomRemoveHandler() {
        $plugin = new CustomPluginStub();
        $this->injector->share($plugin);
        $manager = $this->getPluginManager();
        $manager->registerPlugin(CustomPluginStub::class);
        $manager->registerPluginRemoveHandler(CustomPluginStub::class, function(CustomPluginStub $plugin) {
            $plugin->myCustomPlugin();
        });

        $manager->loadPlugins();

        $manager->removePlugin(CustomPluginStub::class);

        $this->assertSame(1, $plugin->getTimesCalled());
    }

    /**
     * @throws CircularDependencyException
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testRemoveLoadedPluginMatchesHandlerForInterface() {
        $plugin = new class implements CustomPluginInterface {
            private $timesCalled = 0;

            public function myMethod() {
                $this->timesCalled++;
            }

            public function getTimesCalled() {
                return $this->timesCalled;
            }
        };

        $this->injector->share($plugin);
        $manager = $this->getPluginManager();
        $manager->registerPlugin(get_class($plugin));
        $manager->registerPluginRemoveHandler(CustomPluginInterface::class, function(CustomPluginInterface $plugin) {
            $plugin->myMethod();
        });

        $manager->loadPlugins();

        $manager->removePlugin(get_class($plugin));

        $this->assertSame(1, $plugin->getTimesCalled());
    }

    /**
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testRemoveLoadedPluginPassesArgumentsToHandler() {
        $manager = $this->getPluginManager();
        $manager->registerPlugin(PluginStub::class);
        $actual = new stdClass();
        $manager->registerPluginRemoveHandler(
            PluginStub::class,
            function(PluginStub $pluginStub, int $a, bool $b, array $c) use($actual) {
                $actual->plugin = $pluginStub;
                $actual->a = $a;
                $actual->b = $b;
                $actual->c = $c;
            },
            1,
            true,
            [2,3,4]
        );

        $manager->loadPlugins();

        $manager->removePlugin(PluginStub::class);

        $this->assertSame(1, $actual->a);
        $this->assertTrue($actual->b);
        $this->assertSame([2,3,4], $actual->c);
    }

    /**
     * @throws CircularDependencyException
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testRemoveHandlerAcceptsMultipleHandlers() {
        $plugin = new CustomPluginStub();
        $this->injector->share($plugin);

        $manager = $this->getPluginManager();

        $manager->registerPlugin(CustomPluginStub::class);

        $manager->registerPluginRemoveHandler(
            CustomPluginStub::class,
            function(CustomPluginStub $fooPluginStub) {
                $fooPluginStub->myCustomPlugin();
            }
        );
        $manager->registerPluginRemoveHandler(
            CustomPluginStub::class,
            function(CustomPluginStub $fooPluginStub) {
                $fooPluginStub->myCustomPlugin();
            }
        );

        $manager->loadPlugins();

        $manager->removePlugin(CustomPluginStub::class);

        $this->assertSame(2, $plugin->getTimesCalled());
    }

    public function correctPluginMethodsCalledProvider() {
        return [
            [new BootCalledPlugin(), function(BootCalledPlugin $plugin) { return $plugin->wasCalled();
            }],
            [new ServicesRegisteredPlugin(), function(ServicesRegisteredPlugin $plugin) {
                return $plugin->wasCalled();
            }],
            [new EventsRegisteredPlugin(), function(EventsRegisteredPlugin $plugin) {
                return $plugin->wasRegisterCalled();
            }]
        ];
    }

    /**
     * @dataProvider correctPluginMethodsCalledProvider
     *
     * @param Plugin $plugin
     * @param callable $getWasCalled
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws CircularDependencyException
     * @throws ConfigException
     */
    public function testPluginMethodsCalled(Plugin $plugin, callable $getWasCalled) {
        $this->injector->share($plugin);
        $manager = $this->getPluginManager();
        $manager->registerPlugin(get_class($plugin));

        $manager->loadPlugins();

        $this->assertTrue($getWasCalled($plugin));
    }

    /**
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

        $manager->loadPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    /**
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

        $manager->loadPlugins();

        $this->assertSame(1, $plugin->getNumberTimesBootCalled());
    }

    /**
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

        $manager->loadPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    public function testHavePluginsLoadedBeforeLoadingReturnsFalse() {
        $manager = $this->getPluginManager();

        $this->assertFalse(
            $manager->havePluginsLoaded(),
            'Expected loaded to be false before loadPlugins invoked'
        );
    }

    /**
     * @throws CircularDependencyException
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testHavePluginsLoadedAfterLoadingReturnTrue() {
        $this->injector->share($this->injector);
        $this->injector->share($plugin = new RecursivelyDependentPluginStub($this->injector));
        $manager = $this->getPluginManager();

        $manager->registerPlugin(get_class($plugin));

        $manager->loadPlugins();

        $this->assertTrue(
            $manager->havePluginsLoaded(),
            'Expected loaded to be true after loadPlugins invoked'
        );
    }

    /**
     * @throws CircularDependencyException
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testDependentPluginsAlterRegistrationListIfNotPresent() {
        $this->injector->share($this->injector);
        $this->injector->share($plugin = new RecursivelyDependentPluginStub($this->injector));
        $manager = $this->getPluginManager();

        $manager->registerPlugin(get_class($plugin));

        $manager->loadPlugins();

        $registeredPlugins = $manager->getRegisteredPlugins();
        $expected = [
            RecursivelyDependentPluginStub::class,
            FooPluginStub::class,
            FooPluginDependentStub::class
        ];

        $msg = 'Expected dependent Plugins to be added to registered list';
        $this->assertSame($expected, $registeredPlugins, $msg);
    }


    /**
     * @throws CircularDependencyException
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testGettingLoadedPlugins() {
        $pluginStub = new PluginStub();
        $fooStub = new FooPluginStub();
        $this->injector->share($pluginStub);
        $this->injector->share($fooStub);

        $manager = $this->getPluginManager();

        $manager->registerPlugin(PluginStub::class);
        $manager->registerPlugin(FooPluginStub::class);

        $manager->loadPlugins();

        $loadedPlugins = $manager->getLoadedPlugins();
        $expected = [$pluginStub, $fooStub];

        $msg = 'Expected loaded plugins to be the objects that were created';
        $this->assertSame($expected, $loadedPlugins, $msg);
    }

    /**
     * @throws InvalidStateException
     */
    public function testGettingLoadedPluginsBeforeLoadPluginsThrowsException() {
        $manager = $this->getPluginManager();

        $this->expectException(InvalidStateException::class);
        $msg = 'Loaded Plugins may only be gathered after ' . Pluggable::class . '::loadPlugins has been invoked';
        $this->expectExceptionMessage($msg);

        $manager->getLoadedPlugins();
    }

    /**
     * @throws CircularDependencyException
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws NotFoundException
     */
    public function testGettingIndividualLoadedPlugin() {
        $pluginStub = new PluginStub();
        $this->injector->share($pluginStub);

        $manager = $this->getPluginManager();

        $manager->registerPlugin(PluginStub::class);

        $manager->loadPlugins();

        $actual = $manager->getLoadedPlugin(PluginStub::class);

        $msg = 'Expected loaded plugins to be the objects that were created';
        $this->assertSame($pluginStub, $actual, $msg);
    }

    /**
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws NotFoundException
     */
    public function testGettingIndividualLoadedPluginBeforeLoadThrowsException() {
        $pluginStub = new PluginStub();
        $this->injector->share($pluginStub);

        $manager = $this->getPluginManager();

        $manager->registerPlugin(PluginStub::class);

        $this->expectException(InvalidStateException::class);
        $msg = 'Loaded Plugins may only be gathered after ' . Pluggable::class . '::loadPlugins has been invoked';
        $this->expectExceptionMessage($msg);

        $manager->getLoadedPlugin(PluginStub::class);
    }

    /**
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

        $manager->loadPlugins();
    }

    /**
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testPluginDependentPluginDeclaresDependencyThatIsNotPluginThrowsException() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin(PluginDependsNotPluginStub::class);

        $this->expectException(InvalidStateException::class);
        $msg = 'A Plugin, ' . PluginDependsNotPluginStub::class . ', depends ';
        $msg .= 'on a type, ' . Engine::class . ', that does not implement ' . Plugin::class;
        $this->expectExceptionMessage($msg);

        $manager->loadPlugins();
    }

    /**
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testExceptionThrownIfPluginRegisteredAfterLoading() {
        $manager = $this->getPluginManager();

        $manager->loadPlugins();

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
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testRegisteringClassThatIsNotAPluginThrowsException() {
        $manager = $this->getPluginManager();

        $this->expectException(InvalidArgumentException::class);
        $msg = "Attempted to register a Plugin, " . Engine::class . ", that does not ";
        $msg .= "implement the " . Plugin::class . " interface";
        $this->expectExceptionMessage($msg);

        $manager->registerPlugin(Engine::class);
    }

    /**
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testRegisterCustomPluginHandler() {
        $this->injector->share($plugin = new CustomPluginStub());
        $manager = $this->getPluginManager();

        $manager->registerPluginLoadHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPlugin(get_class($plugin));

        $manager->loadPlugins();

        $this->assertSame(1, $plugin->getTimesCalled());
    }

    /**
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testRegisteringMultipleCustomPluginHandlers() {
        $this->injector->share($plugin = new CustomPluginStub());
        $manager = $this->getPluginManager();

        $manager->registerPluginLoadHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPluginLoadHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPlugin(get_class($plugin));

        $manager->loadPlugins();

        $this->assertSame(2, $plugin->getTimesCalled());
    }

    /**
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testCustomHandlerInvokedAfterSystemHandlers() {
        $callOrder = new stdClass();
        $callOrder->callOrder = [];
        CustomPluginOrderStub::setCallOrderObject($callOrder);
        $manager = $this->getPluginManager();

        $manager->registerPluginLoadHandler(
            CustomPluginOrderStub::class,
            function(CustomPluginOrderStub $pluginStub) {
                $pluginStub->customOp();
            }
        );
        $manager->registerPlugin(CustomPluginOrderStub::class);

        $manager->loadPlugins();

        $expected = ['depends', 'services', 'events', 'custom', 'boot'];
        $this->assertSame($expected, $callOrder->callOrder);
    }

    /**
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
        $manager->registerPluginLoadHandler(CustomPluginStub::class, $handler, 'a', 'b', 'c');
        $manager->registerPlugin(CustomPluginStub::class);

        $manager->loadPlugins();

        $this->assertSame(['a', 'b', 'c'], $handlerArgs->data);
    }

    /**
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     * @throws ConfigException
     */
    public function testCustomHandlerHandlesPluginTypesThatAreInterfaces() {
        $manager = $this->getPluginManager();

        $handlerArgs = new stdClass();
        $handlerArgs->data = null;
        $manager->registerPluginLoadHandler(
            CustomPluginInterface::class,
            function(CustomPluginInterface $plugin) use($handlerArgs) {
                $handlerArgs->data = $plugin;
            }
        );
        $plugin = $this->createMock(CustomPluginInterface::class);
        $this->injector->share($plugin);
        $manager->registerPlugin(get_class($plugin));

        $manager->loadPlugins();

        $this->assertSame($plugin, $handlerArgs->data);
    }

    /**
     * @return Generator
     * @throws CircularDependencyException
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function testCustomHandlerReturnsPromise() {
        $manager = $this->getPluginManager();
        $actual = new stdClass();
        $actual->counter = 0;
        $manager->registerPlugin(FooPluginStub::class);
        $manager->registerPluginLoadHandler(FooPluginStub::class, function() use($actual) {
            async(function() use($actual) {
                delay(0);
                $actual->counter++;
                delay(0);
                $actual->counter++;
                delay(0);
                $actual->counter++;
            })->await();
        });

        $manager->loadPlugins();

        $this->assertSame(3, $actual->counter);
    }

    public function testRegisterPluginLogsMessage() {
        $this->getPluginManager()->registerPlugin(PluginStub::class);

        $this->assertTrue($this->logHandler->hasInfoThatContains('Registered Plugin "' . PluginStub::class . '".'));
    }

    public function testRemovingPluginNotLoaded() {
        $this->getPluginManager()->removePlugin(PluginStub::class);

        $this->assertTrue($this->logHandler->hasInfoThatContains('Removed Plugin "' . PluginStub::class . '".'));
    }

    public function testLoadingPluginOnlyIdentifyingInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(PluginStub::class);
        $this->logger->reset();
        $pluginManager->loadPlugins();

        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting to load ' . PluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading ' . PluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading 1 Plugins, including dependencies.'
        ));
    }

    public function testLoadingPluginOnlyInjectorAwareInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(ServicesRegisteredPlugin::class);
        $this->logger->reset();
        $pluginManager->loadPlugins();

        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting to load ' . ServicesRegisteredPlugin::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Wiring object graph for ' . ServicesRegisteredPlugin::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading ' . ServicesRegisteredPlugin::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading 1 Plugins, including dependencies.'
        ));
    }

    public function testLoadingPluginOnlyEventAwareInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(EventsRegisteredPlugin::class);
        $this->logger->reset();
        $pluginManager->loadPlugins();

        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting to load ' . EventsRegisteredPlugin::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Registering event listeners for ' . EventsRegisteredPlugin::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading ' . EventsRegisteredPlugin::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading 1 Plugins, including dependencies.'
        ));
    }

    public function testLoadingPluginOnlyBootableInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(BootCalledPlugin::class);
        $this->logger->reset();
        $pluginManager->loadPlugins();

        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting to load ' . BootCalledPlugin::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting ' . BootCalledPlugin::class . ' boot procedure.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished ' . BootCalledPlugin::class . ' boot procedure.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading ' . BootCalledPlugin::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading 1 Plugins, including dependencies.'
        ));
    }

    public function testLoadingPluginWithPluginDependentPluginInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(FooPluginDependentStub::class);
        $this->logger->reset();
        $pluginManager->loadPlugins();

        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting to load ' . FooPluginDependentStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Loading dependencies for ' . FooPluginDependentStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting to load ' . FooPluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Wiring object graph for ' . FooPluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting ' . FooPluginStub::class . ' boot procedure.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished ' . FooPluginStub::class . ' boot procedure.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading ' . FooPluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading dependencies for ' . FooPluginDependentStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting ' . FooPluginDependentStub::class . ' boot procedure.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished ' . FooPluginDependentStub::class . ' boot procedure.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading ' . FooPluginDependentStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading 2 Plugins, including dependencies.'
        ));
    }

    public function testLoadingPluginOnlyCustomHandler() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(CustomPluginStub::class);
        $pluginManager->registerPluginLoadHandler(
            CustomPluginStub::class,
            function(CustomPluginStub $customPluginStub) {
            }
        );
        $pluginManager->registerPluginLoadHandler(
            CustomPluginStub::class,
            function(CustomPluginStub $customPluginStub) {
            }
        );
        $pluginManager->registerPluginLoadHandler(
            CustomPluginStub::class,
            function(CustomPluginStub $customPluginStub) {
            }
        );
        $this->logger->reset();
        $pluginManager->loadPlugins();

        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Starting to load ' . CustomPluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Found 3 custom handlers for ' . CustomPluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading custom handlers for ' . CustomPluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading ' . CustomPluginStub::class . '.'
        ));
        $this->assertTrue($this->logHandler->hasInfoThatContains(
            'Finished loading 1 Plugins, including dependencies.'
        ));
    }

    public function testPluginsHaveBeenLoadedIfNoRegisteredPlugins() {
        $subject = $this->getPluginManager();

        $subject->loadPlugins();

        $this->assertTrue($subject->havePluginsLoaded());
    }
}
