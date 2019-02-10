<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\ServiceAwarePlugin;
use Auryn\Injector;

class FooPluginStub implements ServiceAwarePlugin, BootablePlugin {

    private $numBootCalled = 0;

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot() : void {
        $this->numBootCalled++;
    }

    public function getNumberTimesBootCalled() : int {
        return $this->numBootCalled;
    }

    /**
     * Register any services that the Plugin provides.
     *
     * @param Injector $injector
     * @return void
     */
    public function registerServices(Injector $injector) : void {
        $injector->share(FooService::class);
    }
}
