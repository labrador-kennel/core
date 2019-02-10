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
use Cspray\Labrador\Exception\InvalidEngineStateException;
use Cspray\Labrador\Plugin\Plugin;
use Cspray\Labrador\Plugin\Pluggable;
use Amp\Loop;

class CoreEngine implements Engine {

    private $emitter;
    private $pluginManager;
    private $eventFactory;
    private $engineState = 'idle';
    private $engineBooted = false;

    /**
     * @param PluginManager $pluginManager
     * @param Emitter $emitter
     * @param EventFactory $eventFactory
     */
    public function __construct(
        PluginManager $pluginManager,
        Emitter $emitter,
        EventFactory $eventFactory = null
    ) {
        $this->pluginManager = $pluginManager;
        $this->emitter = $emitter;
        $this->eventFactory = $eventFactory ?? new StandardEventFactory();
    }

    public function getEmitter() : Emitter {
        return $this->emitter;
    }

    public function onEngineBootup(callable $cb, array $listenerData = []) : self {
        $this->emitter->on(self::ENGINE_BOOTUP_EVENT, $cb, $listenerData);
        return $this;
    }

    /**
     * @param callable $cb
     * @return $this
     */
    public function onAppCleanup(callable $cb, array $listenerData = []) : self {
        $this->emitter->on(self::APP_CLEANUP_EVENT, $cb, $listenerData);
        return $this;
    }

    /**
     * Ensures that the appropriate plugins are booted and then executes the application.
     *
     * @return void
     * @throws InvalidEngineStateException
     */
    public function run(Application $application) : void {
        if ($this->engineState !== 'idle') {
            throw new InvalidEngineStateException('Engine::run() MUST NOT be called while already running.');
        }

        Loop::setErrorHandler(function(\Throwable $error) use($application) {
            $application->exceptionHandler($error);
            Loop::defer(function() use($application) {
                yield $this->emitAppCleanupEvent($application);
            });
        });

        Loop::run(function() use($application) {
            $this->engineState = 'running';

            if (!$this->hasPlugin(get_class($application))) {
                $this->registerPlugin($application);
            }

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
        $promise = $this->emitter->emit($event);
        return $promise;
    }

    private function emitAppCleanupEvent(Application $application) {
        $event = $this->eventFactory->create(self::APP_CLEANUP_EVENT, $application);
        $promise = $this->emitter->emit($event);
        return $promise;
    }

    public function registerPluginHandler(string $pluginType, callable $pluginHandler, ...$arguments): void {
        $this->pluginManager->registerPluginHandler($pluginType, $pluginHandler, $arguments);
    }

    /**
     * @param Plugin $plugin
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function registerPlugin(Plugin $plugin) : Pluggable {
        $this->pluginManager->registerPlugin($plugin);
        return $this;
    }

    /**
     * @param string $name
     * @return void
     */
    public function removePlugin(string $name) : void {
        $this->pluginManager->removePlugin($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasPlugin(string $name) : bool {
        return $this->pluginManager->hasPlugin($name);
    }

    /**
     * @param string $name
     * @return Plugin
     * @throws Exception\NotFoundException
     */
    public function getPlugin(string $name) : Plugin {
        return $this->pluginManager->getPlugin($name);
    }

    /**
     * @return Plugin[]
     */
    public function getPlugins() : iterable {
        return $this->pluginManager->getPlugins();
    }
}
