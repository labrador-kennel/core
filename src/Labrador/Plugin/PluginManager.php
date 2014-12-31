<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

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
        $this->plugins = new PluginCollection();
    }

    public function registerBooter() {
        $cb = function() { $this->getPlugins()->map('boot'); };
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

        $this->plugins->add($plugin);
    }

    public function removePlugin($name) {
        $this->plugins->remove($name);
    }

    public function hasPlugin($name) {
        return $this->plugins->has($name);
    }

    public function getPlugins() {
        return $this->plugins->copy();
    }

    public function getPlugin($name) {
        if (!$this->plugins->has($name)) {
            throw new NotFoundException("Could not find a registered plugin named \"$name\"");
        }
        return $this->plugins->get($name);
    }

}
