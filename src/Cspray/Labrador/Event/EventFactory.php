<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Event;

interface EventFactory {

    public function create(string $eventName, ...$args) : Event;

}