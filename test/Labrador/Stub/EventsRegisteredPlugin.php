<?php


namespace Labrador\Stub;

use Evenement\EventEmitterInterface;
use Labrador\Plugin\EventAwarePlugin;

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
     * Return the name of the plugin; this name should match /[A-Za-z0-9\.\-\_]/
     *
     * @return string
     */
    public function getName() : string {
        return 'events_registered';
    }

    /**
     * Perform any actions that should be
     */
    public function boot() {
        // TODO: Implement boot() method.
    }

}
