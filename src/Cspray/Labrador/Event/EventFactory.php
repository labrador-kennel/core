<?php

declare(strict_types = 1);

/**
 * An interface to abstract the creation of Event objects so that your
 * application can replace all Events triggered by Labrador with your
 * own domain-specific type.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Event;

use League\Event\EventInterface;

interface EventFactory {

    /**
     * If the factory cannot create the $eventName passed throw an InvalidArgumentException.
     *
     * @param string $eventName
     * @param ...$args
     * @return mixed
     * @throws \Cspray\Labrador\Exception\InvalidArgumentException
     */
    public function create(string $eventName, ...$args) : EventInterface;

}