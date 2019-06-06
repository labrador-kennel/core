<?php

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\EventAwarePlugin;
use Cspray\Labrador\AsyncEvent\Emitter;

class EventsRegisteredPlugin implements EventAwarePlugin {

    private $registered = false;
    private $removed = false;

    public function wasRegisterCalled() {
        return $this->registered;
    }

    public function wasRemoveCalled() {
        return $this->removed;
    }

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param Emitter $emitter
     * @return void
     */
    public function registerEventListeners(Emitter $emitter) : void {
        $this->registered = true;
    }

    public function removeEventListeners(Emitter $emitter): void {
        $this->removed = true;
    }
}
