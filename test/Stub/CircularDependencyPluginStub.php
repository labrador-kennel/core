<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Ds\Set;

class CircularDependencyPluginStub implements PluginDependentPlugin {

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return array
     */
    public static function dependsOn() : Set {
        return new Set([RequiresCircularDependentStub::class]);
    }
}
