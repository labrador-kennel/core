<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test;

use function Amp\call;
use Amp\Delayed;
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
use Cspray\Labrador\AsyncEvent\AmpEmitter as EventEmitter;
use Auryn\Injector;
use Auryn\ConfigException;
use Psr\Log\Test\TestLogger;
use stdClass;
use Generator;

class PluginManagerTest extends AsyncTestCase {

    private $emitter;
    /** @var Injector */
    private $injector;

    /** @var TestLogger */
    private $logger;

    public function setUp() {
        parent::setUp();
        $this->emitter = new EventEmitter();
        $this->injector = new Injector();
        $this->logger = new TestLogger();
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
     * @return Generator
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

        yield $manager->loadPlugins();

        $manager->removePlugin(EventsRegisteredPlugin::class);

        $this->assertTrue($plugin->wasRemoveCalled());
    }

    /**
     * @return Generator
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

        yield $manager->loadPlugins();

        $manager->removePlugin(CustomPluginStub::class);

        $this->assertSame(1, $plugin->getTimesCalled());
    }

    /**
     * @return Generator
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

        yield $manager->loadPlugins();

        $manager->removePlugin(get_class($plugin));

        $this->assertSame(1, $plugin->getTimesCalled());
    }

    /**
     * @return Generator
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

        yield $manager->loadPlugins();

        $manager->removePlugin(PluginStub::class);

        $this->assertSame(1, $actual->a);
        $this->assertTrue($actual->b);
        $this->assertSame([2,3,4], $actual->c);
    }

    /**
     * @return Generator
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

        yield $manager->loadPlugins();

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

        yield $manager->loadPlugins();

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
     * @return Generator
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

        yield $manager->loadPlugins();

        $this->assertTrue(
            $manager->havePluginsLoaded(),
            'Expected loaded to be true after loadPlugins invoked'
        );
    }

    /**
     * @return Generator
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

        yield $manager->loadPlugins();

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
     * @return Generator
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

        yield $manager->loadPlugins();

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
     * @return Generator
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

        yield $manager->loadPlugins();

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
    public function testPluginDependentPluginDeclaresDependencyThatIsNotPluginThrowsException() {
        $manager = $this->getPluginManager();

        $manager->registerPlugin(PluginDependsNotPluginStub::class);

        $this->expectException(InvalidStateException::class);
        $msg = 'A Plugin, ' . PluginDependsNotPluginStub::class . ', depends ';
        $msg .= 'on a type, ' . Engine::class . ', that does not implement ' . Plugin::class;
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
     * @return Generator
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

        $manager->registerPluginLoadHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
            $pluginStub->myCustomPlugin();
        });
        $manager->registerPluginLoadHandler(CustomPluginStub::class, function(CustomPluginStub $pluginStub) {
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

        yield $manager->loadPlugins();

        $expected = ['depends', 'services', 'events', 'custom', 'boot'];
        $this->assertSame($expected, $callOrder->callOrder);
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
        $manager->registerPluginLoadHandler(CustomPluginStub::class, $handler, 'a', 'b', 'c');
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
        $manager->registerPluginLoadHandler(
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
     */
    public function testCustomHandlerReturnsPromise() {
        $manager = $this->getPluginManager();
        $actual = new stdClass();
        $actual->counter = 0;
        $manager->registerPlugin(FooPluginStub::class);
        $manager->registerPluginLoadHandler(FooPluginStub::class, function() use($actual) {
            return call(function() use($actual) {
                yield new Delayed(1);
                $actual->counter++;
                yield new Delayed(1);
                $actual->counter++;
                yield new Delayed(1);
                $actual->counter++;
            });
        });

        yield $manager->loadPlugins();

        $this->assertSame(3, $actual->counter);
    }

    public function testRegisterPluginLogsMessage() {
        $this->getPluginManager()->registerPlugin(PluginStub::class);

        $expected = [
            [
                'level' => 'info',
                'message' => 'Registered Plugin "' . PluginStub::class . '".',
                'context' => []
            ]
        ];

        $this->assertSame($expected, $this->logger->records);
    }

    public function testRemovingPluginNotLoaded() {
        $this->getPluginManager()->removePlugin(PluginStub::class);

        $expected = [
            [
                'level' => 'info',
                'message' => 'Removed Plugin "' . PluginStub::class . '".',
                'context' => []
            ]
        ];

        $this->assertSame($expected, $this->logger->records);
    }

    public function testLoadingPluginOnlyIdentifyingInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(PluginStub::class);
        $this->logger->reset();
        yield $pluginManager->loadPlugins();

        $expected = [
            [
                'level' => 'info',
                'message' => 'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting to load ' . PluginStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading ' . PluginStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading 1 Plugins, including dependencies.',
                'context' => []
            ]
        ];

        $this->assertSame($expected, $this->logger->records);
    }

    public function testLoadingPluginOnlyInjectorAwareInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(ServicesRegisteredPlugin::class);
        $this->logger->reset();
        yield $pluginManager->loadPlugins();

        $expected = [
            [
                'level' => 'info',
                'message' => 'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting to load ' . ServicesRegisteredPlugin::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Wiring object graph for ' . ServicesRegisteredPlugin::class . '.',
                'context' => []

            ],
            [
                'level' => 'info',
                'message' => 'Finished loading ' . ServicesRegisteredPlugin::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading 1 Plugins, including dependencies.',
                'context' => []
            ]
        ];

        $this->assertSame($expected, $this->logger->records);
    }

    public function testLoadingPluginOnlyEventAwareInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(EventsRegisteredPlugin::class);
        $this->logger->reset();
        yield $pluginManager->loadPlugins();

        $expected = [
            [
                'level' => 'info',
                'message' => 'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting to load ' . EventsRegisteredPlugin::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Registering event listeners for ' . EventsRegisteredPlugin::class . '.',
                'context' => []

            ],
            [
                'level' => 'info',
                'message' => 'Finished loading ' . EventsRegisteredPlugin::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading 1 Plugins, including dependencies.',
                'context' => []
            ]
        ];

        $this->assertSame($expected, $this->logger->records);
    }

    public function testLoadingPluginOnlyBootableInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(BootCalledPlugin::class);
        $this->logger->reset();
        yield $pluginManager->loadPlugins();

        $expected = [
            [
                'level' => 'info',
                'message' => 'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting to load ' . BootCalledPlugin::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting ' . BootCalledPlugin::class . ' boot procedure.',
                'context' => []

            ],
            [
                'level' => 'info',
                'message' => 'Finished ' . BootCalledPlugin::class . ' boot procedure.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading ' . BootCalledPlugin::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading 1 Plugins, including dependencies.',
                'context' => []
            ]
        ];

        $this->assertSame($expected, $this->logger->records);
    }

    public function testLoadingPluginWithPluginDependentPluginInterface() {
        $pluginManager = $this->getPluginManager();
        $pluginManager->registerPlugin(FooPluginDependentStub::class);
        $this->logger->reset();
        yield $pluginManager->loadPlugins();

        $expected = [
            [
                'level' => 'info',
                'message' => 'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting to load ' . FooPluginDependentStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Loading dependencies for ' . FooPluginDependentStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting to load ' . FooPluginStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Wiring object graph for ' . FooPluginStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting ' . FooPluginStub::class . ' boot procedure.',
                'context' => []

            ],
            [
                'level' => 'info',
                'message' => 'Finished ' . FooPluginStub::class . ' boot procedure.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading ' . FooPluginStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading dependencies for ' . FooPluginDependentStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting ' . FooPluginDependentStub::class . ' boot procedure.',
                'context' => []

            ],
            [
                'level' => 'info',
                'message' => 'Finished ' . FooPluginDependentStub::class . ' boot procedure.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading ' . FooPluginDependentStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading 2 Plugins, including dependencies.',
                'context' => []
            ]
        ];

        $this->assertSame($expected, $this->logger->records);
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
        yield $pluginManager->loadPlugins();

        $expected = [
            [
                'level' => 'info',
                'message' => 'Initiating Plugin loading. Loading 1 registered Plugins, not including dependencies.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Starting to load ' . CustomPluginStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Found 3 custom handlers for ' . CustomPluginStub::class . '.',
                'context' => []

            ],
            [
                'level' => 'info',
                'message' => 'Finished loading custom handlers for ' . CustomPluginStub::class . '.',
                'context' => []

            ],
            [
                'level' => 'info',
                'message' => 'Finished loading ' . CustomPluginStub::class . '.',
                'context' => []
            ],
            [
                'level' => 'info',
                'message' => 'Finished loading 1 Plugins, including dependencies.',
                'context' => []
            ]
        ];

        $this->assertSame($expected, $this->logger->records);
    }
}
