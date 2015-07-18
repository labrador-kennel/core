<?php

declare(strict_types=1);

/**
 * A minimal implementation to trigger specific events and handle plugin registration.
 *
 * @license See LICENSE in source root
 */

namespace Labrador;

use Labrador\Event\{PluginBootEvent, AppExecuteEvent, PluginCleanupEvent, ExceptionThrownEvent};
use Labrador\Plugin\{Plugin, PluginManager};
use Evenement\EventEmitterInterface;

class CoreEngine implements Engine {

    private $emitter;
    private $pluginManager;

    /**
     * @param EventEmitterInterface $emitter
     * @param PluginManager $pluginManager
     */
    public function __construct(EventEmitterInterface $emitter, PluginManager $pluginManager) {
        $this->emitter = $emitter;
        $this->pluginManager = $pluginManager;
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

    /**
     * @param callable $cb
     * @return $this
     */
    public function onPluginBoot(callable $cb) : self {
        $this->emitter->on(self::PLUGIN_BOOT_EVENT, $cb);
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
    public function onPluginCleanup(callable $cb) : self {
        $this->emitter->on(self::PLUGIN_CLEANUP_EVENT, $cb);
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
            $this->emitter->emit(self::PLUGIN_BOOT_EVENT, [new PluginBootEvent(), $this]);
            $this->emitter->emit(self::APP_EXECUTE_EVENT, [new AppExecuteEvent(), $this]);
        } catch (\Exception $exception) {
            $this->emitter->emit(self::EXCEPTION_THROWN_EVENT, [new ExceptionThrownEvent($exception), $this]);
        } finally {
            $this->emitter->emit(self::PLUGIN_CLEANUP_EVENT, [new PluginCleanupEvent(), $this]);
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
