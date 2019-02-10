<?php

declare(strict_types=1);

/**
 * An interface for objects that allow Plugins to be attached or associated with them.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Plugin;

interface Pluggable {

    /**
     * Register a handler for a custom Plugin type that is not natively supported by Labrador.
     *
     * For more information about how custom handlers interact with Plugins during the initialization process please
     * review the /docs/plugins/README.md documentation.
     *
     * @param string $pluginType
     * @param callable $pluginHandler
     * @param mixed ...$arguments
     */
    public function registerPluginHandler(string $pluginType, callable $pluginHandler, ...$arguments) : void;

    /**
     * @param Plugin $plugin
     * @return $this
     */
    public function registerPlugin(Plugin $plugin) : Pluggable;

    /**
     * @param string $name
     */
    public function removePlugin(string $name) : void;

    /**
     * @param string $name
     * @return boolean
     */
    public function hasPlugin(string $name) : bool;

    /**
     * @param string $name
     * @return Plugin
     */
    public function getPlugin(string $name) : Plugin;

    /**
     * An array of Plugin objects associated to the given Pluggable.
     *
     * @return Plugin[]
     */
    public function getPlugins() : iterable;
}
