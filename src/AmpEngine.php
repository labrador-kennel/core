<?php

declare(strict_types=1);

/**
 * The standard Engine implementation triggering all required Labrador events.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\EventFactory;
use Cspray\Labrador\AsyncEvent\StandardEventFactory;
use Cspray\Labrador\Exception\InvalidStateException;
use Amp\Loop;

class AmpEngine implements Engine {

    private $emitter;
    private $eventFactory;
    private $engineState = 'idle';
    private $engineBooted = false;

    /**
     * @param Emitter $emitter
     * @param EventFactory $eventFactory
     */
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
     * @param callable $cb
     * @param array $listenerData
     * @return AmpEngine
     */
    public function onEngineBootup(callable $cb, array $listenerData = []) : self {
        $this->emitter->on(self::ENGINE_BOOTUP_EVENT, $cb, $listenerData);
        return $this;
    }

    /**
     * @param callable $cb
     * @param array $listenerData
     * @return $this
     */
    public function onAppCleanup(callable $cb, array $listenerData = []) : self {
        $this->emitter->on(self::APP_CLEANUP_EVENT, $cb, $listenerData);
        return $this;
    }

    /**
     * Ensures that the appropriate plugins are booted and then executes the application.
     *
     * @param Application $application
     * @return void
     * @throws Exception\InvalidArgumentException
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
        $event = $this->eventFactory->create(self::APP_CLEANUP_EVENT, $application);
        $promise = $this->emitter->emit($event);
        return $promise;
    }

}
