<?php

declare(strict_types=1);

/**
 * This is here to abstract away how plugins are managed for the Engine and
 * to ensure that Engine implementations do not have to require the Injector
 * directly to satisfy the Pluggable interface.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Plugin\{Pluggable, Plugin, ServiceAwarePlugin, EventAwarePlugin, PluginDependentPlugin};
use Cspray\Labrador\Exception\{CircularDependencyException, NotFoundException, PluginDependencyNotProvidedException};
use Auryn\Injector;
use Collections\HashMap;
use Evenement\EventEmitterInterface;

class PluginManager implements Pluggable {

    private $plugins;
    private $emitter;
    private $injector;
    private $booter;
    private $pluginsBooted = false;

    public function __construct(Injector $injector, EventEmitterInterface $emitter) {
        $this->emitter = $emitter;
        $this->injector = $injector;
        $this->plugins = new HashMap();
        $this->booter = $this->getBooter();
        $this->registerBooter();
    }

    private function registerBooter() {
        $cb = function() {
            $this->pluginsBooted = true;
            $this->booter->bootPlugins();
        };
        $cb = $cb->bindTo($this);

        $this->emitter->on(Engine::ENVIRONMENT_INITIALIZE_EVENT, $cb);
    }

    public function registerPlugin(Plugin $plugin) {
        $this->plugins[get_class($plugin)] = $plugin;
        if ($this->pluginsBooted) {
            $this->booter->loadPlugin($plugin);
        }
    }

    public function removePlugin(string $name) {
        unset($this->plugins[$name]);
    }

    public function hasPlugin(string $name) : bool {
        return isset($this->plugins[$name]);
    }

    public function getPlugins() : array {
        return $this->plugins->toArray();
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

            public function __construct(Pluggable $pluggable, Injector $injector, EventEmitterInterface $emitter) {
                $this->pluggable = $pluggable;
                $this->injector = $injector;
                $this->emitter = $emitter;
            }

            public function bootPlugins() {
                foreach($this->pluggable->getPlugins() as $plugin) {
                    $this->loadPlugin($plugin);
                }
            }

            public function loadPlugin(Plugin $plugin) {
                if ($this->notLoaded($plugin)) {
                    $this->startLoading($plugin);
                    $this->handlePluginDependencies($plugin);
                    $this->handlePluginServices($plugin);
                    $this->handlePluginEvents($plugin);
                    $plugin->boot();
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
                            throw new PluginDependencyNotProvidedException(sprintf($msg, get_class($plugin), $reqPluginName));
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
        };
    }

}
