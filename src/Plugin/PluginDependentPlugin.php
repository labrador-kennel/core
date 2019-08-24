<?php declare(strict_types = 1);

namespace Cspray\Labrador\Plugin;

use Ds\Set;

/**
 * A Plugin that depends on another Plugin being present and loaded before it can be loaded itself.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE file in project root
 */
interface PluginDependentPlugin extends Plugin {

    /**
     * Return an array of fully qualified class names for Plugins that should be loaded by the Pluggable the
     * implementing Plugin is attached to.
     *
     * If you return a value that is not a valid class name that implements the Plugin interface an exception will be
     * thrown during the Plugin loading process.
     *
     * @return Set<string>
     */
    public static function dependsOn() : Set;
}
