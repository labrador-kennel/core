<?php

namespace Labrador\Event;

abstract class Event {

    private $name;
    private $stopEventPropagation = false;

    public function __construct($name) {
        $this->name = (string) $name;
    }

    public function getName() {
        return $this->name;
    }

    public function isPropagationStopped() {
        return $this->stopEventPropagation;
    }

    public function stopPropagation() {
        $this->stopEventPropagation = true;
    }

}