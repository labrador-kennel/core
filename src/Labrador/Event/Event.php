<?php

declare(strict_types=1);

namespace Labrador\Event;

abstract class Event {

    private $name;
    private $stopEventPropagation = false;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName() : string {
        return $this->name;
    }

    public function isPropagationStopped() : bool {
        return $this->stopEventPropagation;
    }

    public function stopPropagation() {
        $this->stopEventPropagation = true;
    }

}
