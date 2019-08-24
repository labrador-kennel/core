<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Amp\Promise;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\EventFactory;
use Cspray\Labrador\AsyncEvent\StandardEventFactory;
use Cspray\Labrador\Exception\InvalidStateException;
use Amp\Loop;
use Psr\Log\LoggerAwareTrait;
use Throwable;

/**
 * An implementation of the Engine interface running on the global amphp Loop.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
class AmpEngine implements Engine {

    use LoggerAwareTrait;

    private $emitter;
    private $eventFactory;
    private $engineState;
    private $engineBooted = false;

    public function __construct(
        Emitter $emitter,
        EventFactory $eventFactory = null
    ) {
        $this->emitter = $emitter;
        $this->eventFactory = $eventFactory ?? new StandardEventFactory();
        $this->engineState = EngineState::Idle();
    }

    public function getState() : EngineState {
        return $this->engineState;
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
        $this->emitter->on(self::START_UP_EVENT, $cb, $listenerData);
        return $this;
    }

    /**
     * @param callable $cb function(Cspray\Labrador\AsyncEvent\Event, ...$listenerData) : Promise|Generator|void
     * @param array $listenerData
     * @return $this
     */
    public function onEngineShutdown(callable $cb, array $listenerData = []) : self {
        $this->emitter->on(self::SHUT_DOWN_EVENT, $cb, $listenerData);
        return $this;
    }

    /**
     * @param Application $application
     * @throws InvalidStateException
     */
    public function run(Application $application) : void {
        if (!$this->engineState->isIdling()) {
            /** @var InvalidStateException $exception */
            $exception = Exceptions::createException(
                Exceptions::ENGINE_ERR_MULTIPLE_RUN_CALLS,
                null
            );
            throw $exception;
        }

        Loop::setErrorHandler(function(Throwable $error) use($application) {
            $this->logger->alert(
                sprintf(
                    'The Application threw an exception: %s "%s"',
                    get_class($error),
                    $error->getMessage()
                ),
                ['file' => $error->getFile(), 'line' => $error->getLine()]
            );
            $application->exceptionHandler($error);
            Loop::defer(function() use($application) {
                $this->logger->info('Starting Application cleanup process.');
                yield $this->emitEngineShutDownEvent($application);
                $this->logger->info('Completed Application cleanup process. Engine shutting down.');
                $this->engineState = EngineState::Crashed();
            });
        });


        Loop::run(function() use($application) {
            $this->engineState = EngineState::Running();

            $this->emitter->once(self::START_UP_EVENT, function() use($application) {
                $this->logger->info('Starting Plugin loading process.');
                yield $application->loadPlugins();
                $this->logger->info('Completed Plugin loading process.');
            });

            if (!$this->engineBooted) {
                yield $this->emitEngineStartUpEvent();
                $this->engineBooted = true;
            }

            $this->logger->info('Starting Application process.');
            yield $application->execute();
            $this->logger->info('Completed Application process.');

            $this->logger->info('Starting Application cleanup process.');
            yield $this->emitEngineShutDownEvent($application);
            $this->logger->info('Completed Application cleanup process. Engine shutting down.');
            $this->engineState = EngineState::Idle();
        });
    }

    /**
     * @return Promise
     * @throws Exception\InvalidArgumentException
     */
    private function emitEngineStartUpEvent() : Promise {
        $event = $this->eventFactory->create(self::START_UP_EVENT, $this);
        return $this->emitter->emit($event);
    }

    /**
     * @param Application $application
     * @return Promise
     * @throws Exception\InvalidArgumentException
     */
    private function emitEngineShutDownEvent(Application $application) : Promise {
        $event = $this->eventFactory->create(self::SHUT_DOWN_EVENT, $application);
        $promise = $this->emitter->emit($event);
        return $promise;
    }
}
