<?php


namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\ServiceAwarePlugin;
use Auryn\Injector;


class ServicesRegisteredPlugin implements ServiceAwarePlugin {

    private $called = false;

    public function wasCalled() {
        return $this->called;
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
