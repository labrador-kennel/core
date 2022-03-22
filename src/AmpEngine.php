<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncEvent\EventFactory;
use Cspray\Labrador\AsyncEvent\StandardEventFactory;
use Cspray\Labrador\Exception\InvalidStateException;
use Psr\Log\LoggerAwareTrait;
use Revolt\EventLoop;
use Throwable;

/**
 * An implementation of the Engine interface running on the global amphp Loop.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
final class AmpEngine implements Engine {

    use LoggerAwareTrait;

    private EventEmitter $emitter;
    private EventFactory $eventFactory;
    private EngineState $engineState;
    private bool $engineBooted = false;

    public function __construct(
        EventEmitter $emitter,
        EventFactory $eventFactory = null
    ) {
        $this->emitter = $emitter;
        $this->eventFactory = $eventFactory ?? new StandardEventFactory();
        $this->engineState = EngineState::Idle();
    }

    public function getState() : EngineState {
        return $this->engineState;
    }

    public function getEmitter() : EventEmitter {
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

        $signalWatcher = null;
        if (extension_loaded('pcntl')) {
            $signalWatcher = EventLoop::onSignal(SIGINT, function() use($application, &$signalWatcher) {
                if ($this->engineState->isRunning()) {
                    $application->stop()->await();
                    $this->emitEngineShutDownEvent($application);
                }
                EventLoop::disable($signalWatcher);
                EventLoop::getDriver()->stop();
                exit;
            });
            EventLoop::unreference($signalWatcher);
        }

        EventLoop::setErrorHandler(fn(Throwable $err) => $this->handleThrowable($err, $application, $signalWatcher));

        EventLoop::queue(function() use($application, $signalWatcher) {
            $this->engineState = EngineState::Running();

            $this->logger->info('Starting Plugin loading process.');
            $application->loadPlugins();
            $this->logger->info('Completed Plugin loading process.');

            if (!$this->engineBooted) {
                $this->emitEngineStartUpEvent();
                $this->engineBooted = true;
            }

            $this->logger->info('Starting Application process.');
            $application->start()->await();
            $this->logger->info('Completed Application process.');

            $this->logger->info('Starting Application cleanup process.');
            $this->emitEngineShutDownEvent($application);
            $this->logger->info('Completed Application cleanup process. Engine shutting down.');
            $this->engineState = EngineState::Idle();
            if (isset($signalWatcher)) {
                EventLoop::disable($signalWatcher);
            }
        });
        EventLoop::run();
    }

    /**
     * @return void
     * @throws Exception\InvalidTypeException
     */
    private function emitEngineStartUpEvent() : void {
        $event = $this->eventFactory->create(self::START_UP_EVENT, $this);
        $this->emitter->emit($event)->await();
    }

    /**
     * @param Application $application
     * @return void
     * @throws Exception\InvalidTypeException
     */
    private function emitEngineShutDownEvent(Application $application) : void {
        $event = $this->eventFactory->create(self::SHUT_DOWN_EVENT, $application);
        $this->emitter->emit($event)->await();
    }

    private function handleThrowable(
        Throwable $throwable,
        Application $application,
        ?string $signalWatcher = null
    ) : void {
        if (isset($signalWatcher)) {
            EventLoop::disable($signalWatcher);
        }

        $this->engineState = EngineState::Crashed();
        try {
            $application->handleException($throwable);
            $this->emitEngineShutDownEvent($application);
        } catch (Throwable $throwable) {
            $this->logger->critical(
                'An exception was thrown from Application::handleException. This method must not throw exceptions.'
            );
        } finally {
            EventLoop::getDriver()->stop();
        }
    }
}
