<?php

declare(strict_types=1);

/**
 * This is here to abstract away how plugins are managed for the Engine and
 * to ensure that Engine implementations do not have to require the Injector
 * directly to satisfy the Pluggable interface.
 *
 * @license See LICENSE in source root
 * @internal Specifically for use by internal Engine implementations
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
use Cspray\Labrador\Plugin\ServiceAwarePlugin;
use Cspray\Labrador\Plugin\EventAwarePlugin;
use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Exception\PluginDependencyNotProvidedException;
use Cspray\Labrador\AsyncEvent\Emitter;
use Auryn\Injector;
use ArrayObject;

/**
 * It is HIGHLY recommended that if you create a Plugin it winds up
 * being registered with this Pluggable; the standard Labrador Engines
 * proxy all Pluggable methods to an instance of this class.
 */
class PluginManager implements Pluggable {

    private $plugins;
    private $emitter;
    private $injector;
    private $booter;
    private $pluginsBooted = false;

    /**
     * @param Injector $injector
     * @param Emitter $emitter
     */
    public function __construct(Injector $injector, Emitter $emitter) {
        $this->emitter = $emitter;
        $this->injector = $injector;
        $this->plugins = new ArrayObject();
        $this->booter = $this->getBooter();
        $this->registerBooter();
    }

    private function registerBooter() {
        $cb = function() {
            $this->pluginsBooted = true;
            $this->booter->bootPlugins();
        };
        $cb = $cb->bindTo($this);

        $this->emitter->on(Engine::ENGINE_BOOTUP_EVENT, $cb);
    }

    public function registerPluginHandler(string $pluginType, callable $handler, ...$arguments) : void {
        $this->booter->registerPluginHandler($pluginType, $handler, ...$arguments);
    }

    public function registerPlugin(Plugin $plugin) : Pluggable {
        $pluginName = get_class($plugin);
        if ($this->hasPlugin($pluginName)) {
            $msg = "A Plugin with name $pluginName has already been registered and may not be registered again.";
            throw new InvalidArgumentException($msg);
        }

        $this->plugins[$pluginName] = $plugin;
        if ($this->pluginsBooted) {
            $this->booter->loadPlugin($plugin);
        }

        return $this;
    }

    public function removePlugin(string $name) : void {
        unset($this->plugins[$name]);
    }

    public function hasPlugin(string $name) : bool {
        return isset($this->plugins[$name]);
    }

    public function getPlugins() : iterable {
        return $this->plugins;
    }

    public function getPlugin(string $name) : Plugin {
        if (!isset($this->plugins[$name])) {
            $msg = 'Could not find a registered plugin named "%s"';
            throw new NotFoundException(sprintf($msg, $name));
        }

        return $this->plugins[$name];
    }

    private function getBooter() {
        return new class($this, $this->injector, $this->emitter) {

            private $loading = [];
            private $loaded = [];
            private $pluggable;
            private $injector;
            private $emitter;
            private $pluginHandlers = [
                'custom' => []
            ];

            public function __construct(Pluggable $pluggable, Injector $injector, Emitter $emitter) {
                $this->pluggable = $pluggable;
                $this->injector = $injector;
                $this->emitter = $emitter;
            }

            public function registerPluginHandler(string $pluginType, callable $handler, ...$arguments) {
                if (!isset($this->pluginHandlers['custom'][$pluginType])) {
                    $this->pluginHandlers['custom'][$pluginType] = [];
                }
                $this->pluginHandlers['custom'][$pluginType][] = [$handler, $arguments];
            }

            public function bootPlugins() {
                foreach ($this->pluggable->getPlugins() as $plugin) {
                    $this->loadPlugin($plugin);
                }
            }

            public function loadPlugin(Plugin $plugin) {
                if ($this->notLoaded($plugin)) {
                    $this->startLoading($plugin);
                    $this->handlePluginDependencies($plugin);
                    $this->handlePluginServices($plugin);
                    $this->handlePluginEvents($plugin);
                    $this->handleCustomPluginHandlers($plugin);
                    $this->bootPlugin($plugin);
                    $this->finishLoading($plugin);
                }
            }

            private function notLoaded(Plugin $plugin) {
                return !in_array(get_class($plugin), $this->loaded);
            }

            private function startLoading(Plugin $plugin) {
                $this->loading[] = get_class($plugin);
            }

            private function finishLoading(Plugin $plugin) {
                $name = get_class($plugin);
                $this->loading = array_diff($this->loading, [$name]);
                $this->loaded[] = $name;
            }

            private function isLoading(Plugin $plugin) {
                return in_array(get_class($plugin), $this->loading);
            }

            private function handlePluginDependencies(Plugin $plugin) {
                if ($plugin instanceof PluginDependentPlugin) {
                    foreach ($plugin->dependsOn() as $reqPluginName) {
                        if (!$this->pluggable->hasPlugin($reqPluginName)) {
                            $msg = '%s requires a plugin that is not registered: %s.';
                            $msg = sprintf($msg, get_class($plugin), $reqPluginName);
                            throw new PluginDependencyNotProvidedException($msg);
                        }

                        $reqPlugin = $this->pluggable->getPlugin($reqPluginName);
                        if ($this->isLoading($reqPlugin)) {
                            $msg = 'A circular dependency was found with %s requiring %s.';
                            throw new CircularDependencyException(sprintf($msg, get_class($plugin), $reqPluginName));
                        }
                        $this->loadPlugin($reqPlugin);
                    }
                }
            }

            private function handlePluginServices(Plugin $plugin) {
                if ($plugin instanceof ServiceAwarePlugin) {
                    $plugin->registerServices($this->injector);
                }
            }

            private function handlePluginEvents(Plugin $plugin) {
                if ($plugin instanceof EventAwarePlugin) {
                    $plugin->registerEventListeners($this->emitter);
                }
            }

            private function handleCustomPluginHandlers(Plugin $plugin) {
                $pluginClass = get_class($plugin);
                foreach ($this->pluginHandlers['custom'] as $type => $pluginHandlers) {
                    if ($pluginClass === $type || $plugin instanceof $type) {
                        foreach ($pluginHandlers as $pluginHandlerData) {
                            $pluginHandler = $pluginHandlerData[0];
                            $pluginHandlerArgs = $pluginHandlerData[1];
                            $pluginHandler($plugin, ...$pluginHandlerArgs);
                        }
                    }
                }
            }

            private function bootPlugin(Plugin $plugin) {
                if ($plugin instanceof BootablePlugin) {
                    $plugin->boot();
                }
            }
        };
    }
}
