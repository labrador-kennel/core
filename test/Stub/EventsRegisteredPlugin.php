<?php

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\EventAwarePlugin;
use Cspray\Labrador\AsyncEvent\Emitter;

class EventsRegisteredPlugin implements EventAwarePlugin {

    private $called = false;

    public function wasCalled() {
        return $this->called;
    }

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param Emitter $emitter
     * @return void
     */
    public function registerEventListeners(Emitter $emitter) : void {
        $this->called = true;
    }
}
