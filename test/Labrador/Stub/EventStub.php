<?php

namespace Labrador\Stub;

use Labrador\Event\Event;

class EventStub extends Event {

    public function __construct() {
        parent::__construct('event_stub');
    }

}