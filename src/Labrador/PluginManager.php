<?php

declare(strict_types=1);

/**
 * This is here to abstract away how plugins are managed for the Engine and
 * to ensure that Engine implementations do not have to require the Injector
 * directly to satisfy the Pluggable interface.
 *
 * @license See LICENSE in source root
 */

namespace Labrador;

use Labrador\Plugin\{Pluggable, Plugin, ServiceAwarePlugin, EventAwarePlugin};
use Labrador\Exception\NotFoundException;
use Auryn\Injector;
use Collections\HashMap;
use Evenement\EventEmitterInterface;

class PluginManager implements Pluggable {

    private $plugins;
    private $emitter;
    private $injector;

    public function __construct(Injector $injector, EventEmitterInterface $emitter) {
        $this->emitter = $emitter;
        $this->injector = $injector;
        $this->plugins = new HashMap();
        $this->registerBooter();
    }

    private function registerBooter() {
        $cb = function() { $this->getBooter()->bootPlugins(); };
        $cb = $cb->bindTo($this);

        $this->emitter->on(Engine::PLUGIN_BOOT_EVENT, $cb);
    }

    public function registerPlugin(Plugin $plugin) {
        $this->plugins[get_class($plugin)] = $plugin;
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
            private $pluggable;
            private $injector;
            private $emitter;

            public function __construct(Pluggable $pluggable, Injector $injector, EventEmitterInterface $emitter) {
                $this->pluggable = $pluggable;
                $this->injector = $injector;
                $this->emitter = $emitter;
            }

            public function bootPlugins() {
                $plugins = $this->pluggable->getPlugins();

                // We are executing each of these three methods in separate loops on purpose
                // We want all plugins to register services, then register event listeners, and then boot
                // This is because Plugins may wind up depending on other plugins. This ensures
                // that all of the services a given Plugin may need are registered
                foreach($plugins as $plugin) {
                    if ($plugin instanceof ServiceAwarePlugin) {
                        $plugin->registerServices($this->injector);
                    }
                }

                foreach ($plugins as $plugin) {
                    if ($plugin instanceof EventAwarePlugin) {
                        $plugin->registerEventListeners($this->emitter);
                    }
                }

                foreach ($plugins as $plugin) { /** @var Plugin $plugin */
                    $plugin->boot();
                }
            }
        };
    }

}
