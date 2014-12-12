<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

use Countable;
use IteratorAggregate;

class PluginCollection implements Countable, IteratorAggregate {

    private $plugins = [];

    public function isEmpty() {
        return empty($this->plugins);
    }

    public function add(Plugin $plugin) {
        $this->plugins[$plugin->getName()] = $plugin;
        return $this;
    }

    public function has($name) {
        return array_key_exists((string) $name, $this->plugins);
    }

    public function get($name) {
        return $this->has($name) ? $this->plugins[(string) $name] : null;
    }

    public function remove($name) {
        unset($this->plugins[(string) $name]);
    }

    public function map($method) {
        $return = [];
        foreach ($this->plugins as $plugin) {
            if (is_callable($method)) {
                $return[] = $method($plugin);
            } else {
                $return[] = $plugin->$method();
            }
        }

        return $return;
    }

    public function copy() {
        $copy = new PluginCollection();
        foreach ($this->plugins as $plugin) {
            $copy->add($plugin);
        }

        return $copy;
    }

    public function toArray() {
        return array_values($this->plugins);
    }

    public function getIterator() {
        return new \ArrayIterator($this->toArray());
    }

    public function count() {
        return count($this->plugins);
    }

}
