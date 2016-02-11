<?php

declare(strict_types=1);

/**
 * The standard Engine implementation triggering all required Labrador events.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Event\{EventFactory, StandardEventFactory};
use Cspray\Labrador\Plugin\Plugin;
use League\Event\EmitterInterface;
use Exception as PhpException;

class CoreEngine implements Engine {

    private $emitter;
    private $pluginManager;
    private $eventFactory;

    private $engineBooted = false;

    /**
     * @param PluginManager $pluginManager
     * @param EmitterInterface $emitter
     * @param EventFactory $eventFactory
     */
    public function __construct(
        PluginManager $pluginManager,
        EmitterInterface $emitter,
        EventFactory $eventFactory = null
    ) {
        $this->emitter = $emitter;
        $this->pluginManager = $pluginManager;
        $this->eventFactory = $eventFactory ?? new StandardEventFactory();
    }

    public function getEmitter() : EmitterInterface {
        return $this->emitter;
    }

    public function onEngineBootup(callable $cb, int $priority = EmitterInterface::P_NORMAL) : self {
        $this->emitter->addListener(self::ENGINE_BOOTUP_EVENT, $cb, $priority);
        return $this;
    }

    /**
     * @param callable $cb
     * @return $this
     */
    public function onAppExecute(callable $cb, int $priority = EmitterInterface::P_NORMAL) : self {
        $this->emitter->addListener(self::APP_EXECUTE_EVENT, $cb, $priority);
        return $this;
    }

    /**
     * @param callable $cb
     * @return $this
     */
    public function onAppCleanup(callable $cb, int $priority = EmitterInterface::P_NORMAL) : self {
        $this->emitter->addListener(self::APP_CLEANUP_EVENT, $cb, $priority);
        return $this;
    }

    /**
     * @param callable $cb
     * @return $this
     */
    public function onExceptionThrown(callable $cb, int $priority = EmitterInterface::P_NORMAL) : self {
        $this->emitter->addListener(self::EXCEPTION_THROWN_EVENT, $cb, $priority);
        return $this;
    }

    /**
     * Ensures that the appropriate plugins are booted and then executes the application.
     *
     * @return void
     */
    public function run() {
        try {
            if (!$this->engineBooted) {
                $this->bootEngine();
            }

            $this->executeApp();
        } catch (PhpException $exception) {
            $this->handleException($exception);
        } finally {
            $this->cleanupApp();
        }
    }

    private function bootEngine() {
        $envInitEvent = $this->eventFactory->create(self::ENGINE_BOOTUP_EVENT);
        $eventArgs = $this->eventArgs(self::ENGINE_BOOTUP_EVENT);
        $this->emitter->emit($envInitEvent, $this, ...$eventArgs);
        $this->engineBooted = true;
    }

    private function executeApp() {
        $appExecuteEvent = $this->eventFactory->create(self::APP_EXECUTE_EVENT);
        $eventArgs = $this->eventArgs(self::APP_EXECUTE_EVENT);
        $this->emitter->emit($appExecuteEvent, $this, ...$eventArgs);
    }

    private function handleException(PhpException $exception) {
        $exceptionEvent = $this->eventFactory->create(self::EXCEPTION_THROWN_EVENT, $exception);
        $eventArgs = $this->eventArgs(self::EXCEPTION_THROWN_EVENT);
        $this->emitter->emit($exceptionEvent, $this, ...$eventArgs);
    }

    private function cleanupApp() {
        $appCleanupEvent = $this->eventFactory->create(self::APP_CLEANUP_EVENT);
        $eventArgs = $this->eventArgs(self::APP_CLEANUP_EVENT);
        $this->emitter->emit($appCleanupEvent, $this, ...$eventArgs);
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

    protected function eventArgs(string $event) : array {
        return [];
    }

}
