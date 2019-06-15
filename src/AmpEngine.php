<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\EventFactory;
use Cspray\Labrador\AsyncEvent\StandardEventFactory;
use Cspray\Labrador\Exception\InvalidStateException;
use Amp\Loop;

/**
 * An implementation of the Engine interface running on the global amphp Loop.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
class AmpEngine implements Engine {

    private $emitter;
    private $eventFactory;
    private $engineState = 'idle';
    private $engineBooted = false;

    public function __construct(
        Emitter $emitter,
        EventFactory $eventFactory = null
    ) {
        $this->emitter = $emitter;
        $this->eventFactory = $eventFactory ?? new StandardEventFactory();
    }

    public function getEmitter() : Emitter {
        return $this->emitter;
    }

    /**
     * @param callable $cb function(Cspray\Labrador\AsyncEvent\Event, ...$listenerData) : Promise|Generator|void
     * @param array $listenerData
     * @return AmpEngine
     */
    public function onEngineBootup(callable $cb, array $listenerData = []) : self {
        $this->emitter->on(self::ENGINE_BOOTUP_EVENT, $cb, $listenerData);
        return $this;
    }

    /**
     * @param callable $cb function(Cspray\Labrador\AsyncEvent\Event, ...$listenerData) : Promise|Generator|void
     * @param array $listenerData
     * @return $this
     */
    public function onEngineShutdown(callable $cb, array $listenerData = []) : self {
        $this->emitter->on(self::ENGINE_SHUTDOWN_EVENT, $cb, $listenerData);
        return $this;
    }

    /**
     * @param Application $application
     * @return void
     * @throws InvalidStateException
     */
    public function run(Application $application) : void {
        if ($this->engineState !== 'idle') {
            throw new InvalidStateException('Engine::run() MUST NOT be called while already running.');
        }

        Loop::setErrorHandler(function(\Throwable $error) use($application) {
            $application->exceptionHandler($error);
            Loop::defer(function() use($application) {
                yield $this->emitAppCleanupEvent($application);
            });
        });

        $this->emitter->once(self::ENGINE_BOOTUP_EVENT, function() {
        });

        Loop::run(function() use($application) {
            $this->engineState = 'running';


            if (!$this->engineBooted) {
                yield $this->emitEngineBootupEvent();
                $this->engineBooted = true;
            }

            yield $application->execute();
            yield $this->emitAppCleanupEvent($application);
            $this->engineState = 'idle';
        });
    }

    private function emitEngineBootupEvent() {
        $event = $this->eventFactory->create(self::ENGINE_BOOTUP_EVENT, $this);
        return $this->emitter->emit($event);
    }

    private function emitAppCleanupEvent(Application $application) {
        $event = $this->eventFactory->create(self::ENGINE_SHUTDOWN_EVENT, $application);
        $promise = $this->emitter->emit($event);
        return $promise;
    }

}
