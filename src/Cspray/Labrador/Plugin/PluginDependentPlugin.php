<?php

declare(strict_types = 1);

/**
 * A Plugin that depends on another Plugin being present and loaded before this
 * Plugin can be booted, services can be registered or event listeners can be
 * added.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Plugin;

interface PluginDependentPlugin extends Plugin {

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return array
     */
    public function dependsOn() : array;

}