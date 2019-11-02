<?php


namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Engine;
use Cspray\Labrador\Plugin\PluginDependentPlugin;

class PluginDependsNotPluginStub implements PluginDependentPlugin {

    public static function dependsOn(): array {
        return [Engine::class];
    }
}
