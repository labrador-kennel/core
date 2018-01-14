<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\PluginDependentPlugin;

class CircularDependencyPluginStub implements PluginDependentPlugin {

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return array
     */
    public function dependsOn() : iterable {
        return [RequiresCircularDependentStub::class];
    }

}