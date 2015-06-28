<?php

declare(strict_types=1);

/**
 * An interface for objects that allow plugins to be attached or associated with them.
 *
 * @license See LICENSE in source root
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
    public function removePlugin(string $name);

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
    public function getPlugins() : array;

}
