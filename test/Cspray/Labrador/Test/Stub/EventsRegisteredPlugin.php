<?php

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\EventAwarePlugin;
use Evenement\EventEmitterInterface;

class EventsRegisteredPlugin implements EventAwarePlugin {

    private $called = false;

    public function wasCalled() {
        return $this->called;
    }

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param EventEmitterInterface $emitter
     * @return void
     */
    public function registerEventListeners(EventEmitterInterface $emitter) {
        $this->called = true;
    }

    /**
     * Perform any actions that should be
     */
    public function boot() {
        // TODO: Implement boot() method.
    }

}
