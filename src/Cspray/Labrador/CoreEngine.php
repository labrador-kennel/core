<?php

declare(strict_types=1);

/**
 * A minimal implementation to trigger specific events and handle plugin registration.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Event\{
    EnvironmentInitializeEvent,
    PluginBootEvent,
    AppExecuteEvent,
    PluginCleanupEvent,
    ExceptionThrownEvent,
    EventFactory,
    StandardEventFactory
};
use Cspray\Labrador\Plugin\Plugin;
use Evenement\EventEmitterInterface;
use Telluris\Environment;

class CoreEngine implements Engine {

    private $environment;
    private $emitter;
    private $pluginManager;
    private $eventFactory;

    /**
     * @param Environment $environment
     * @param PluginManager $pluginManager
     * @param EventEmitterInterface $emitter
     * @param EventFactory $eventFactory
     */
    public function __construct(
        Environment $environment,
        PluginManager $pluginManager,
        EventEmitterInterface $emitter,
        EventFactory $eventFactory = null
    ) {
        $this->environment = $environment;
        $this->emitter = $emitter;
        $this->pluginManager = $pluginManager;
        $this->eventFactory = $eventFactory ?? new StandardEventFactory();
    }

    /**
     * @return string
     */
    public function getName() : string {
        return 'labrador-core';
    }

    /**
     * @return string
     */
    public function getVersion() : string {
        return '0.1.0-alpha';
    }

    public function getEnvironment() : Environment {
        return $this->environment;
    }

    public function onEnvironmentInitialize(callable $cb) : self {
        $this->emitter->on(self::ENVIRONMENT_INITIALIZE_EVENT, $cb);
        return $this;
    }

    /**
     * @param callable $cb
     * @return $this
     */
    public function onAppExecute(callable $cb) : self {
        $this->emitter->on(self::APP_EXECUTE_EVENT, $cb);
        return $this;
    }

    /**
     * @param callable $cb
     * @return $this
     */
    public function onAppCleanup(callable $cb) : self {
        $this->emitter->on(self::APP_CLEANUP_EVENT, $cb);
        return $this;
    }

    /**
     * @param callable $cb
     * @return $this
     */
    public function onExceptionThrown(callable $cb) : self {
        $this->emitter->on(self::EXCEPTION_THROWN_EVENT, $cb);
        return $this;
    }

    /**
     * Ensures that the appropriate plugins are booted and then executes the application.
     *
     * @return void
     */
    public function run() {
        try {
            $envInitEvent = $this->eventFactory->create(self::ENVIRONMENT_INITIALIZE_EVENT, $this->environment);
            $this->emitter->emit(self::ENVIRONMENT_INITIALIZE_EVENT, [$envInitEvent, $this]);

            $appExecuteEvent = $this->eventFactory->create(self::APP_EXECUTE_EVENT);
            $this->emitter->emit(self::APP_EXECUTE_EVENT, [$appExecuteEvent, $this]);
        } catch (\Exception $exception) {
            $exceptionEvent = $this->eventFactory->create(self::EXCEPTION_THROWN_EVENT, $exception);
            $this->emitter->emit(self::EXCEPTION_THROWN_EVENT, [$exceptionEvent, $this]);
        } finally {
            $appCleanupEvent = $this->eventFactory->create(self::APP_CLEANUP_EVENT);
            $this->emitter->emit(self::APP_CLEANUP_EVENT, [$appCleanupEvent, $this]);
        }
    }

    /**
     * @param Plugin $plugin
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function registerPlugin(Plugin $plugin) : self {
        $this->pluginManager->registerPlugin($plugin);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removePlugin(string $name) : self {
        $this->pluginManager->removePlugin($name);
        return $this;
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
    public function getPlugins() : array {
        return $this->pluginManager->getPlugins();
    }

}