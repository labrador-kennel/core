<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Event;

use Cspray\Labrador\Engine;
use Cspray\Labrador\Exception\InvalidTypeException;
use League\Event\EventInterface;
use Cspray\Labrador\Exception\InvalidArgumentException;

class StandardEventFactory implements EventFactory {

    private $knownEventsMap = [
        Engine::ENGINE_BOOTUP_EVENT => EngineBootupEvent::class,
        Engine::APP_EXECUTE_EVENT => AppExecuteEvent::class,
        Engine::APP_CLEANUP_EVENT => AppCleanupEvent::class,
        Engine::EXCEPTION_THROWN_EVENT => ExceptionThrownEvent::class
    ];

    private $eventFactories = [];

    public function create(string $eventName, ...$args) : EventInterface {
        if (isset($this->eventFactories[$eventName])) {
            $event = $this->eventFactories[$eventName](...$args);

            if (!$event instanceof EventInterface) {
                $msg = 'Factory functions MUST return an instance of %s but "%s" returned "%s".';
                throw new InvalidTypeException(sprintf($msg, EventInterface::class, $eventName, gettype($event)));
            }

            if ($event->getName() !== $eventName) {
                $msg = 'Factory functions MUST return an instance of %s with the same name as "%s"';
                throw new InvalidTypeException(sprintf($msg, EventInterface::class, $eventName));
            }

            return $event;
        }

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

    public function register(string $eventName, callable $factoryFunction) {
        if (isset($this->knownEventsMap[$eventName])) {
            throw new InvalidArgumentException('You may not register a core event.');
        }
        $this->eventFactories[$eventName] = $factoryFunction;
    }

}