<?php

declare(strict_types=1);

/**
 * The standard Engine implementation triggering all required Labrador events.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Amp\Promise;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\EventFactory;
use Cspray\Labrador\AsyncEvent\StandardEventFactory;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Plugin\Plugin;
use Amp\Loop;
use Cspray\Labrador\Plugin\PluginManager;

class AmpEngine implements Engine {

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
            yield $this->loadPlugins();
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

    public function registerPluginHandler(string $pluginType, callable $pluginHandler, ...$arguments): void {
        $this->pluginManager->registerPluginHandler($pluginType, $pluginHandler, $arguments);
    }

    public function registerPlugin(string $plugin) : void {
        $this->pluginManager->registerPlugin($plugin);
    }

    public function removePlugin(string $name) : Promise {
        return $this->pluginManager->removePlugin($name);
    }

    public function hasPlugin(string $name) : bool {
        return $this->pluginManager->hasPlugin($name);
    }

    public function getPlugin(string $name) : Plugin {
        return $this->pluginManager->getPlugin($name);
    }

    public function getPlugins() : iterable {
        return $this->pluginManager->getPlugins();
    }

    public function loadPlugins(): Promise {
        return $this->pluginManager->loadPlugins();
    }

    /**
     * Register a handler for a custom Plugin type to be invoked when loadPlugins is invoked.
     *
     * @param string $pluginType
     * @param callable $pluginHandler function(YourPluginType $plugin, ...$arguments) : Promise|Generator|void {}
     * @param mixed ...$arguments
     */
    public function registerPluginLoadHandler(string $pluginType, callable $pluginHandler, ...$arguments): void
    {
        // TODO: Implement registerPluginLoadHandler() method.
    }

    /**
     * Register a handler for a custom Plugin type to be invoked when removePlugin is called with a type that matches
     * the $pluginType.
     *
     * If plugins have not yet been loaded when the target Plugin is removed this callback will not be invoked.
     *
     * @param string $pluginType
     * @param callable $pluginHandler function(YourPluginType plugin, ...$arguments) : Promise|Generator|void {}
     * @param mixed ...$arguments
     */
    public function registerPluginRemoveHandler(string $pluginType, callable $pluginHandler, ...$arguments): void
    {
        // TODO: Implement registerPluginRemoveHandler() method.
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasPluginBeenRegistered(string $name): bool
    {
        // TODO: Implement hasPluginBeenRegistered() method.
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasPluginBeenLoaded(string $name): bool
    {
        // TODO: Implement hasPluginBeenLoaded() method.
    }

    /**
     * Attempt to retrieve a Plugin object.
     *
     * If the Plugin could not be found a NotFoundException SHOULD be thrown as the state of Plugins should be known to
     * the developer and a Plugin expected but not present is likely an error in configuration or Application setup and
     * should be addressed immediately.
     *
     * If loadPlugins has not been invoked then an InvalidStateException MUST be thrown as the loading process must be
     * completed before the corresponding Plugin object is available.
     *
     * @param string $name
     * @return Plugin
     * @throws NotFoundException
     * @throws InvalidStateException
     */
    public function getLoadedPlugin(string $name): Plugin
    {
        // TODO: Implement getLoadedPlugin() method.
    }

    /**
     * An array of Plugin objects associated to the given Pluggable.
     *
     * If loadPlugins has not been invoked an InvalidStateException MUST be thrown as the loading process must be
     * completed before Plugin objects are available and this is a distinct case separate from there not being any
     * Plugins after the loading process making an empty array ill-suited for this error condition.
     *
     * @return Plugin[]
     * @throws InvalidStateException
     */
    public function getLoadedPlugins(): array
    {
        // TODO: Implement getLoadedPlugins() method.
    }

    /**
     * An array of Plugin names that will be loaded when loadPlugins is called.
     *
     * @return string[]
     */
    public function getRegisteredPlugins(): array
    {
        // TODO: Implement getRegisteredPlugins() method.
    }
}
