<?php declare(strict_types=1);

namespace Cspray\Labrador\Plugin;

use Amp\Promise;
use Cspray\Labrador\AsyncEvent\Emitter;
use Auryn\Injector;

use function Amp\call;
use Cspray\Labrador\Exceptions;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * De facto Pluggable implementation that manages the lifecycle of Plugins for all out-of-the-box Applications.
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

    private $plugins = [];
    private $loadHandlers = [];
    private $removeHandlers = [];
    private $loading = [];

    private $pluginsLoaded = false;

    public function __construct(Injector $injector, Emitter $emitter) {
        $this->injector = $injector;
        $this->emitter = $emitter;
    }

    public function registerPluginLoadHandler(string $pluginType, callable $pluginHandler, ...$arguments): void {
        $this->loadHandlers[$pluginType][] = [$pluginHandler, $arguments];
    }

    public function registerPlugin(string $plugin) : void {
        if ($exception = $this->guardRegisterPluginIsValid($plugin)) {
            throw $exception;
        }
        $this->plugins[$plugin] = null;
        $this->logger->info(sprintf('Registered Plugin "%s".', $plugin));
    }

    private function guardRegisterPluginIsValid(string $plugin) : ?\Throwable {
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
            $this->logger->info(sprintf(
                'Initiating Plugin loading. Loading %d registered Plugins, not including dependencies.',
                count($this->plugins)
            ));
            foreach ($this->getRegisteredPlugins() as $pluginName) {
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
        if (isset($this->plugins[$name])) {
            $plugin = $this->plugins[$name];
            if ($plugin instanceof EventAwarePlugin) {
                $plugin->removeEventListeners($this->emitter);
            }

            foreach ($this->removeHandlers as $pluginType => $handlers) {
                if ($plugin instanceof $pluginType) {
                    foreach ($handlers as list($handler, $args)) {
                        $handler($plugin, ...$args);
                    }
                }
            }
        }
        $this->logger->info(sprintf('Removed Plugin "%s".', $name));
        unset($this->plugins[$name]);
    }

    public function registerPluginRemoveHandler(string $pluginType, callable $pluginHandler, ...$arguments) : void {
        $this->removeHandlers[$pluginType][] = [$pluginHandler, $arguments];
    }

    public function hasPluginBeenRegistered(string $name) : bool {
        return array_key_exists($name, $this->plugins);
    }

    public function havePluginsLoaded() : bool {
        return $this->pluginsLoaded;
    }

    public function getLoadedPlugin(string $name) : Plugin {
        if (!array_key_exists($name, $this->plugins)) {
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_PLUGIN_NOT_FOUND,
                null,
                $name
            );
            throw $exception;
        }

        if (!isset($this->plugins[$name])) {
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD,
                null
            );
            throw $exception;
        }

        return $this->plugins[$name];
    }

    public function getLoadedPlugins() : array {
        if (!$this->havePluginsLoaded()) {
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD,
                null
            );
            throw $exception;
        }
        return array_values($this->plugins);
    }

    public function getRegisteredPlugins() : array {
        return array_keys($this->plugins);
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
        return !isset($this->plugins[$plugin]);
    }

    private function startLoading(string $plugin) {
        $this->logger->info(sprintf('Starting to load %s.', $plugin));
        $this->loading[] = $plugin;
    }

    private function finishLoading(Plugin $plugin) {
        $this->logger->info(sprintf('Finished loading %s.', get_class($plugin)));
        $name = get_class($plugin);
        $this->loading = array_diff($this->loading, [$name]);
        $this->plugins[$name] = $plugin;
    }

    private function isLoading(string $plugin) {
        return in_array($plugin, $this->loading);
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

    private function guardLoadingValidPluginDependency(string $plugin, string $reqPluginName) : ?\Throwable {
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
                    foreach ($pluginHandlers as list($pluginHandler, $pluginHandlerArgs)) {
                        yield call($pluginHandler, $plugin, ...$pluginHandlerArgs);
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
