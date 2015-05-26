<?php

namespace Labrador\Event;

abstract class PropagatableEvent {

    private $stopEventPropagation = false;

    public function isPropagationStopped() {
        return $this->stopEventPropagation;
    }

    public function stopPropagation() {
        $this->stopEventPropagation = true;
    }

}