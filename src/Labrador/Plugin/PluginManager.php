<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

use Collections\HashMap;
use Labrador\Engine;
use Labrador\Exception\InvalidArgumentException;
use Labrador\Exception\NotFoundException;
use Auryn\Injector;
use Evenement\EventEmitterInterface;

class PluginManager implements Pluggable {

    private $plugins;
    private $emitter;
    private $injector;

    public function __construct(Injector $injector, EventEmitterInterface $emitter) {
        $this->emitter = $emitter;
        $this->injector = $injector;
        $this->plugins = new HashMap();
    }

    public function registerBooter() {
        $plugins = $this->getPlugins();
        $cb = function() use($plugins) {
            foreach($plugins as $plugin) { /** @var Plugin $plugin */
                $plugin->boot();
            }
        };
        $this->emitter->on(Engine::PLUGIN_BOOT_EVENT, $cb);
    }

    public function registerPlugin(Plugin $plugin) {
        if (preg_match('/[^A-Za-z0-9\.\-\_]/', $plugin->getName())) {
            throw new InvalidArgumentException('A valid plugin name may only contain letters, numbers, periods, underscores, and dashes [A-Za-z0-9\.\-\_]');
        }

        if ($plugin instanceof ServiceAwarePlugin) {
            $plugin->registerServices($this->injector);
        }

        if ($plugin instanceof EventAwarePlugin) {
            $plugin->registerEventListeners($this->emitter);
        }

        $this->plugins[$plugin->getName()] = $plugin;
    }

    public function removePlugin($name) {
        unset($this->plugins[$name]);
    }

    public function hasPlugin($name) {
        return isset($this->plugins[$name]);
    }

    public function getPlugins() {
        return $this->plugins->toArray();
    }

    public function getPlugin($name) {
        if (!isset($this->plugins[$name])) {
            throw new NotFoundException("Could not find a registered plugin named \"$name\"");
        }
        return $this->plugins[$name];
    }

}
