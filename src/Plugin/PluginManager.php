<?php declare(strict_types=1);

namespace Cspray\Labrador\Plugin;

use Amp\Promise;
use Cspray\Labrador\AsyncEvent\Emitter;
use Auryn\Injector;

use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;
use Ds\Map;
use Ds\Pair;
use Ds\Set;
use Ds\Vector;
use function Amp\call;
use Cspray\Labrador\Exceptions;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * The default Pluggable implementation that manages the lifecycle of Plugins for all out-of-the-box Applications.
 *
 * It is HIGHLY recommended that if you implement your own Pluggable interface that you delegate the actual
 * responsibilities for handling the lifecycle of the Plugin to an instance of this object; it is well tested and
 * implements the Plugin loading process in a known order that other Plugins may be reliant upon.
 *
 * Another important aspect of using this class over implementing the Pluggable methods in your own code is that this
 * object helps abstract away the fact that we must ask for an Injector as a constructor dependency. By keeping that
 * Injector dependency outside of your application and consuming code there's less opportunity for your Injector to
 * be turned into a Service Locator.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE in source root
 */
final class PluginManager implements Pluggable, LoggerAwareInterface {

    use LoggerAwareTrait;

    private $emitter;
    private $injector;

    private $plugins;
    private $loadHandlers;
    private $removeHandlers;
    private $loading;

    private $pluginsLoaded = false;

    /**
     * Constructs the PluginManager with dependencies required to be provided to certain Plugin types.
     *
     * There are 2 primary reasons for asking for the Injector in this class; the first is that we are required to
     * construct Plugins from a string and constructing objects in a fashion that all known dependencies are provided
     * is a natural responsibility of the Injector. The second is that the InjectorAwarePlugin requires that an Injector
     * be provided during the plugin loading process.
     *
     * @param Injector $injector
     * @param Emitter $emitter
     */
    public function __construct(Injector $injector, Emitter $emitter) {
        $this->injector = $injector;
        $this->emitter = $emitter;
        $this->loading = new Set();
        $this->plugins = new Map();
        $this->loadHandlers = new Map();
        $this->removeHandlers = new Map();
    }

    public function registerPluginLoadHandler(string $pluginType, callable $pluginHandler, ...$arguments): void {
        if (!$this->loadHandlers->hasKey($pluginType)) {
            $this->loadHandlers->put($pluginType, new Vector());
        }

        $vector = $this->loadHandlers->get($pluginType);
        $vector->push(new Pair($pluginHandler, $arguments));
    }

    /**
     * @param string $plugin
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function registerPlugin(string $plugin) : void {
        /** @var InvalidArgumentException|InvalidStateException $exception */
        if ($exception = $this->guardRegisterPluginIsValid($plugin)) {
            throw $exception;
        }
        $this->plugins->put($plugin, null);
        $this->logger->info(sprintf('Registered Plugin "%s".', $plugin));
    }

    /**
     * @param string $plugin
     * @return Exception|null
     * @throws InvalidArgumentException
     */
    private function guardRegisterPluginIsValid(string $plugin) : ?Exception {
        if ($this->hasPluginBeenRegistered($plugin)) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_HAS_BEEN_REGISTERED,
                null,
                $plugin
            );
        }

        if ($this->pluginsLoaded) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_REGISTER_PLUGIN_POSTLOAD,
                null
            );
        }

        $implementedTypes = class_implements($plugin);
        if (!in_array(Plugin::class, $implementedTypes)) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_REGISTER_NOT_PLUGIN_TYPE,
                null,
                $plugin
            );
        }

        return null;
    }

    public function loadPlugins() : Promise {
        return call(function() {
            $plugins = $this->getRegisteredPlugins();
            $this->logger->info(sprintf(
                'Initiating Plugin loading. Loading %d registered Plugins, not including dependencies.',
                count($plugins)
            ));
            foreach ($plugins as $pluginName) {
                yield $this->loadPlugin($pluginName);
            }
            $this->logger->info(sprintf(
                'Finished loading %d Plugins, including dependencies.',
                count($this->plugins)
            ));
            $this->pluginsLoaded = true;
        });
    }

    public function removePlugin(string $name) : void {
        if ($this->plugins->hasKey($name)) {
            $plugin = $this->plugins->get($name);
            if ($plugin instanceof EventAwarePlugin) {
                $plugin->removeEventListeners($this->emitter);
            }

            foreach ($this->removeHandlers as $pluginType => $handlers) {
                if ($plugin instanceof $pluginType) {
                    foreach ($handlers as $handlerPair) {
                        ($handlerPair->key)($plugin, ...$handlerPair->value);
                    }
                }
            }

            $this->plugins->remove($name);
        }
        $this->logger->info(sprintf('Removed Plugin "%s".', $name));
    }

    public function registerPluginRemoveHandler(string $pluginType, callable $pluginHandler, ...$arguments) : void {
        if (!$this->removeHandlers->hasKey($pluginType)) {
            $this->removeHandlers->put($pluginType, new Vector());
        }

        $vector = $this->removeHandlers->get($pluginType) ;
        $vector->push(new Pair($pluginHandler, $arguments));
    }

    public function hasPluginBeenRegistered(string $name) : bool {
        return $this->plugins->hasKey($name);
    }

    public function havePluginsLoaded() : bool {
        return $this->pluginsLoaded;
    }

    /**
     * @param string $name
     * @return Plugin
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function getLoadedPlugin(string $name) : Plugin {
        if (!$this->hasPluginBeenRegistered($name)) {
            /** @var InvalidArgumentException $exception */
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_PLUGIN_NOT_FOUND,
                null,
                $name
            );
            throw $exception;
        }

        $plugin = $this->plugins->get($name);
        if ($plugin === null) {
            /** @var InvalidStateException $exception */
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD,
                null
            );
            throw $exception;
        }

        return $plugin;
    }

    /**
     * @return Set
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function getLoadedPlugins() : Set {
        if (!$this->havePluginsLoaded()) {
            /** @var InvalidStateException $exception */
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD,
                null
            );
            throw $exception;
        }
        return new Set($this->plugins->values());
    }

    public function getRegisteredPlugins() : Set {
        return $this->plugins->keys();
    }

    private function loadPlugin(string $pluginName) : Promise {
        return call(function() use($pluginName) {
            if ($this->notLoaded($pluginName)) {
                $this->startLoading($pluginName);
                yield $this->handlePluginDependencies($pluginName);

                $plugin = $this->injector->make($pluginName);

                $this->handlePluginServices($plugin);
                $this->handlePluginEvents($plugin);
                yield $this->handleCustomPluginHandlers($plugin);
                yield $this->bootPlugin($plugin);
                $this->finishLoading($plugin);
            }
        });
    }

    private function notLoaded(string $plugin) {
        return !$this->plugins->hasKey($plugin) || $this->plugins->get($plugin) === null;
    }

    private function startLoading(string $plugin) {
        $this->logger->info(sprintf('Starting to load %s.', $plugin));
        $this->loading->add($plugin);
    }

    private function finishLoading(Plugin $plugin) {
        $this->logger->info(sprintf('Finished loading %s.', get_class($plugin)));
        $name = get_class($plugin);
        $this->loading->remove($name);
        $this->plugins->put($name, $plugin);
    }

    private function isLoading(string $plugin) {
        return $this->loading->contains($plugin);
    }

    private function handlePluginDependencies(string $plugin) : Promise {
        return call(function() use($plugin) {
            $implementedTypes = class_implements($plugin);
            if (in_array(PluginDependentPlugin::class, $implementedTypes)) {
                foreach (call_user_func([$plugin, 'dependsOn']) as $reqPluginName) {
                    if ($exception = $this->guardLoadingValidPluginDependency($plugin, $reqPluginName)) {
                        throw $exception;
                    }

                    $this->logger->info(sprintf(
                        'Loading dependencies for %s.',
                        $plugin
                    ));
                    yield $this->loadPlugin($reqPluginName);
                    $this->logger->info(sprintf(
                        'Finished loading dependencies for %s.',
                        $plugin
                    ));
                }
            }
        });
    }

    /**
     * @param string $plugin
     * @param string $reqPluginName
     * @return Exception|null
     * @throws InvalidArgumentException
     */
    private function guardLoadingValidPluginDependency(string $plugin, string $reqPluginName) : ?Exception {
        if ($this->isLoading($reqPluginName)) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_CIRCULAR_DEPENDENCY,
                null,
                $plugin,
                $reqPluginName
            );
        }

        $dependencyTypes = class_implements($reqPluginName);
        if (!in_array(Plugin::class, $dependencyTypes)) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_DEPENDENCY_NOT_PLUGIN_TYPE,
                null,
                $plugin,
                $reqPluginName
            );
        }

        return null;
    }

    private function handlePluginServices(Plugin $plugin) {
        if ($plugin instanceof InjectorAwarePlugin) {
            $this->logger->info(sprintf(
                'Wiring object graph for %s.',
                get_class($plugin)
            ));
            $plugin->wireObjectGraph($this->injector);
        }
    }

    private function handlePluginEvents(Plugin $plugin) {
        if ($plugin instanceof EventAwarePlugin) {
            $this->logger->info(sprintf(
                'Registering event listeners for %s.',
                get_class($plugin)
            ));
            $plugin->registerEventListeners($this->emitter);
        }
    }

    private function handleCustomPluginHandlers(Plugin $plugin) : Promise {
        return call(function() use($plugin) {
            foreach ($this->loadHandlers as $type => $pluginHandlers) {
                if ($plugin instanceof $type) {
                    $pluginName = get_class($plugin);
                    $this->logger->info(sprintf(
                        'Found %d custom handlers for %s.',
                        count($pluginHandlers),
                        $pluginName
                    ));
                    foreach ($pluginHandlers as $handlerPair) {
                        yield call($handlerPair->key, $plugin, ...$handlerPair->value);
                    }
                    $this->logger->info(sprintf(
                        'Finished loading custom handlers for %s.',
                        $pluginName
                    ));
                }
            }
        });
    }

    private function bootPlugin(Plugin $plugin) : Promise {
        return call(function() use($plugin) {
            if ($plugin instanceof BootablePlugin) {
                $pluginName = get_class($plugin);
                $this->logger->info(sprintf(
                    'Starting %s boot procedure.',
                    $pluginName
                ));
                yield $plugin->boot();
                $this->logger->info(sprintf(
                    'Finished %s boot procedure.',
                    $pluginName
                ));
            }
        });
    }
}
