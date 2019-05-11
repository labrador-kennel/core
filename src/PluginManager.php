<?php

declare(strict_types=1);

/**
 * The de facto implementation of the Pluggable interface that manages the lifecycle of a Plugin.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Amp\Promise;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
use Cspray\Labrador\Plugin\InjectorAwarePlugin;
use Cspray\Labrador\Plugin\EventAwarePlugin;
use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Exception\PluginDependencyNotProvidedException;
use Cspray\Labrador\AsyncEvent\Emitter;
use Auryn\Injector;
use ArrayObject;

use function Amp\call;

/**
 * It is HIGHLY recommended that if you implement your own Pluggable interface that you delegate the actual
 * responsibilities for handling the lifecycle of the Plugin to an instance of this object; it is well tested and
 * implements the Plugin loading process in a known order that other Plugins may be reliant upon.
 *
 * Another important aspect of using this class over implementing the Pluggable methods in your own code is that this
 * object helps abstract away the fact that we must ask for an Injector as a constructor dependency. By keeping that
 * Injector dependency outside of your application and consuming code there's less opportunity for your Injector to
 * be turned into a Service Locator.
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
    }

    public function registerPluginHandler(string $pluginType, callable $handler, ...$arguments) : void {
        $this->booter->registerPluginHandler($pluginType, $handler, ...$arguments);
    }

    public function registerPlugin(Plugin $plugin) : void {
        $pluginName = get_class($plugin);
        if ($this->hasPlugin($pluginName)) {
            $msg = "A Plugin with name $pluginName has already been registered and may not be registered again.";
            throw new InvalidArgumentException($msg);
        }

        if ($this->pluginsBooted) {
            $msg = 'Plugins have already been loaded and you MUST NOT register plugins after this has taken place.';
            throw new InvalidStateException($msg);
        }
        $this->plugins[$pluginName] = $plugin;
    }

    public function loadPlugins(): Promise {
        return call(function() {
            yield $this->booter->bootPlugins();
            $this->pluginsBooted = true;
        });
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

            public function bootPlugins() : Promise {
                return call(function() {
                    foreach ($this->pluggable->getPlugins() as $plugin) {
                        yield $this->loadPlugin($plugin);
                    }
                });
            }

            public function loadPlugin(Plugin $plugin) : Promise {
                return call(function() use($plugin) {
                    if ($this->notLoaded($plugin)) {
                        $this->startLoading($plugin);
                        yield $this->handlePluginDependencies($plugin);
                        $this->handlePluginServices($plugin);
                        $this->handlePluginEvents($plugin);
                        $this->handleCustomPluginHandlers($plugin);
                        yield $this->bootPlugin($plugin);
                        $this->finishLoading($plugin);
                    }
                });
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

            private function handlePluginDependencies(Plugin $plugin) : Promise {
                return call(function() use($plugin) {
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
                                $msg = sprintf($msg, get_class($plugin), $reqPluginName);
                                throw new CircularDependencyException($msg);
                            }
                            yield $this->loadPlugin($reqPlugin);
                        }
                    }
                });
            }

            private function handlePluginServices(Plugin $plugin) {
                if ($plugin instanceof InjectorAwarePlugin) {
                    $plugin->wireObjectGraph($this->injector);
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

            private function bootPlugin(Plugin $plugin) : Promise {
                return call(function() use($plugin) {
                    if ($plugin instanceof BootablePlugin) {
                        yield call(function() use($plugin) {
                            return $this->injector->execute($plugin->boot());
                        });
                    }
                });
            }
        };
    }
}
