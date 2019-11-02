<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Auryn\Injector;

class RecursivelyDependentPluginStub implements PluginDependentPlugin, BootablePlugin {

    private $injector;
    private $dependsOnProvided;

    public function __construct(Injector $injector) {
        $this->injector = $injector;
    }

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot() : Promise {
        $injectorInfo = $this->injector->inspect();
        $shares = $injectorInfo[Injector::I_SHARES];
        $this->dependsOnProvided = array_key_exists('cspray\labrador\test\stub\fooservice', $shares);
        return new Success();
    }

    public function wasDependsOnProvided() {
        return $this->dependsOnProvided;
    }

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return array
     */
    public static function dependsOn() : array {
        return [FooPluginDependentStub::class];
    }
}
