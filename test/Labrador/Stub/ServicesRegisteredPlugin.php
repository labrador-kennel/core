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
     * Return the name of the plugin; this name should match /[A-Za-z0-9\.\-\_]/
     *
     * @return string
     */
    public function getName() : string {
        return 'services-plugin-stub';
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
