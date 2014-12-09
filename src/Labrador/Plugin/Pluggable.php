<?php

/**
 * An interface for objects that allow plugins to be attached or associated with them.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

interface Pluggable {

    /**
     * @param Plugin $plugin
     * @return $this
     */
    public function registerPlugin(Plugin $plugin);

    /**
     * @param string $name
     */
    public function removePlugin($name);

    /**
     * @param string $name
     * @return boolean
     */
    public function hasPlugin($name);

    /**
     * @param string $name
     * @return Plugin
     */
    public function getPlugin($name);

    /**
     * The plugin collection returned should not also be used to manage the plugins
     * for this pluggable; meaning adding plugins to the returned PluginCollection
     * should not also add them to the object implementing this interface.
     *
     * @return PluginCollection
     */
    public function getPlugins();

}
