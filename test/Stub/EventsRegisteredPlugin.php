<?php

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\EventAwarePlugin;
use League\Event\EmitterInterface;

class EventsRegisteredPlugin implements EventAwarePlugin {

    private $called = false;

    public function wasCalled() {
        return $this->called;
    }

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param EmitterInterface $emitter
     * @return void
     */
    public function registerEventListeners(EmitterInterface $emitter) {
        $this->called = true;
    }

}
