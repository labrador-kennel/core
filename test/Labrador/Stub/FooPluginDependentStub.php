<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Labrador\Stub;

use Auryn\Injector;
use Labrador\Plugin\PluginDependentPlugin;

class FooPluginDependentStub implements PluginDependentPlugin {

    private $injector;
    private $dependsOnProvided;

    public function __construct(Injector $injector) {
        $this->injector = $injector;
    }

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot() {
        $shares = $this->injector->inspect();
        $this->dependsOnProvided = array_key_exists('labrador\stub\fooservice', $shares[Injector::I_SHARES]);
    }

    public function wasDependsOnProvided() {
        return $this->dependsOnProvided;
    }

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return array
     */
    public function dependsOn() : array {
        return [FooPluginStub::class];
    }
}