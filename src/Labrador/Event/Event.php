<?php

declare(strict_types=1);

/**
 * An abstract class that allows an event to mark that further listeners for the
 * given event should not be triggered.
 *
 * @license See LICENSE in source root
 */

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
