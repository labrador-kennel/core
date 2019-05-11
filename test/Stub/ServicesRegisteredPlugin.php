<?php


namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\InjectorAwarePlugin;
use Auryn\Injector;

class ServicesRegisteredPlugin implements InjectorAwarePlugin {

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
    public function wireObjectGraph(Injector $injector) : void {
        $this->called = true;
    }
}
