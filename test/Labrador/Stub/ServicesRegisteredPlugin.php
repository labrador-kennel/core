<?php


namespace Labrador\Stub;

use Auryn\Injector;
use Labrador\Plugin\ServiceAwarePlugin;

class ServicesRegisteredPlugin implements ServiceAwarePlugin {

    private $called = false;

    public function wasCalled() {
        return $this->called;
    }

    /**
     * Perform any actions that should be
     */
    public function boot() {
        // TODO: Implement boot() method.
    }

    /**
     * Register any services that the Plugin provides.
     *
     * @param Injector $injector
     * @return void
     */
    public function registerServices(Injector $injector) {
        $this->called = true;
    }
}
