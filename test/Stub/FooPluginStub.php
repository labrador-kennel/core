<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\InjectorAwarePlugin;
use Auryn\Injector;

class FooPluginStub implements InjectorAwarePlugin, BootablePlugin {

    private $numBootCalled = 0;

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot() : Promise {
        $this->numBootCalled++;
        return new Success();
    }

    public function getNumberTimesBootCalled() : int {
        return $this->numBootCalled;
    }

    public function wireObjectGraph(Injector $injector) : void {
        $injector->share(FooService::class);
    }
}
