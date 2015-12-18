<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Event;

use Cspray\Labrador\Engine;
use League\Event\EventInterface;
use Cspray\Labrador\Exception\InvalidArgumentException;

class StandardEventFactory implements EventFactory {

    private $knownEventsMap = [
        Engine::ENGINE_BOOTUP_EVENT => EngineBootupEvent::class,
        Engine::APP_EXECUTE_EVENT => AppExecuteEvent::class,
        Engine::APP_CLEANUP_EVENT => AppCleanupEvent::class,
        Engine::EXCEPTION_THROWN_EVENT => ExceptionThrownEvent::class
    ];

    public function create(string $eventName, ...$args) : EventInterface {
        if (!isset($this->knownEventsMap[$eventName])) {
            throw new InvalidArgumentException("$eventName is not known to this factory at this time.");
        }

        $eventClass = $this->knownEventsMap[$eventName];
        if (empty($args)) {
            return new $eventClass();
        } else {
            $r = new \ReflectionClass($eventClass);
            return $r->newInstanceArgs($args);
        }
    }

}