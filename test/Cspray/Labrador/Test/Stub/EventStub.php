<?php

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Event\Event;

class EventStub extends Event {

    public function __construct() {
        parent::__construct('event_stub');
    }

}