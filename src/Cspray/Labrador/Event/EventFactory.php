<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Event;

use League\Event\EventInterface;

interface EventFactory {

    public function create(string $eventName, ...$args) : EventInterface;

}