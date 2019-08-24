<?php


namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Engine;
use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Ds\Set;

class PluginDependsNotPluginStub implements PluginDependentPlugin {

    public static function dependsOn(): Set {
        return new Set([Engine::class]);
    }
}
