<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Labrador\Stub;

use Auryn\Injector;
use Labrador\Plugin\ServiceAwarePlugin;

class FooPluginStub implements ServiceAwarePlugin {

    private $numBootCalled = 0;

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot() {
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
    public function registerServices(Injector $injector) {
        $injector->share(FooService::class);
    }

}