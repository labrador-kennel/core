<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\PluginDependentPlugin;

class RequiresCircularDependentStub implements PluginDependentPlugin {

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return array
     */
    public static function dependsOn() : iterable {
        return [CircularDependencyPluginStub::class];
    }
}
