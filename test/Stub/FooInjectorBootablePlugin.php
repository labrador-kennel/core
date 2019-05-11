<?php


namespace Cspray\Labrador\Test\Stub;

use Auryn\Injector;
use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\InjectorAwarePlugin;

class FooInjectorBootablePlugin implements InjectorAwarePlugin, BootablePlugin {

    private $fooService;
    private $bootInjectedService;

    public function __construct(FooService $fooService) {
        $this->fooService = $fooService;
    }

    /**
     * Return a callable that will be invoked using Auryn's Injector::execute API.
     *
     * By invoking the callable with your application's Injector you can typehint your callable with any service that
     * has been wired by your object graph OR if this object is also a PluginDependentPlugin the services provided by
     * those dependent plugins. Your callable will also be invoked on the event loop and can yield or return a promise
     * and will work as expected.
     *
     * It is very important that your callable only typehints against objects known to be wired in your container. If
     * you typehint a scalar value or a type that cannot be instantiated by the Injector an exception will be thrown.
     *
     * @return callable
     */
    public function boot(): callable {
        return function(FooService $fooService) {
            $this->bootInjectedService = $fooService;
        };
    }

    public function wireObjectGraph(Injector $injector): void {
        $injector->share($this->fooService);
    }

    public function getBootInjectedService() {
        return $this->bootInjectedService;
    }
}
