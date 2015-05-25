<?php

/**
 * A minimal implementation to trigger specific events and handle
 * plugin registration.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Labrador\Event;
use Labrador\Plugin;
use Evenement\EventEmitterInterface;

class CoreEngine implements Engine {

    private $emitter;
    private $pluginManager;

    /**
     * @param EventEmitterInterface $emitter
     * @param Plugin\PluginManager $pluginManager
     */
    public function __construct(EventEmitterInterface $emitter, Plugin\PluginManager $pluginManager) {
        $this->emitter = $emitter;
        $this->pluginManager = $pluginManager;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'Labrador Core';
    }

    /**
     * @return string
     */
    public function getVersion() {
        return '0.1.0-alpha';
    }

    /**
     * @param callable $cb
     * @return void
     */
    public function onPluginBoot(callable $cb) {
        $this->emitter->on(self::PLUGIN_BOOT_EVENT, $cb);
    }

    /**
     * @param callable $cb
     * @return void
     */
    public function onAppExecute(callable $cb) {
        $this->emitter->on(self::APP_EXECUTE_EVENT, $cb);
    }

    /**
     * @param callable $cb
     * @return void
     */
    public function onPluginCleanup(callable $cb) {
        $this->emitter->on(self::PLUGIN_CLEANUP_EVENT, $cb);
    }

    /**
     * @param callable $cb
     * @return void
     */
    public function onExceptionThrown(callable $cb) {
        $this->emitter->on(self::EXCEPTION_THROWN_EVENT, $cb);
    }

    /**
     * Ensures that the appropriate plugins are booted and then executes the application.
     *
     * @return void
     */
    public function run() {
        try {
            $this->pluginManager->registerBooter();
            $this->emitter->emit(self::PLUGIN_BOOT_EVENT, [new Event\PluginBootEvent($this)]);
            $this->emitter->emit(self::APP_EXECUTE_EVENT, [new Event\AppExecuteEvent($this)]);
        } catch (\Exception $exception) {
            $this->emitter->emit(self::EXCEPTION_THROWN_EVENT, [new Event\ExceptionThrownEvent($this, $exception)]);
        } finally {
            $this->emitter->emit(self::PLUGIN_CLEANUP_EVENT, [new Event\PluginCleanupEvent($this)]);
        }
    }

    /**
     * @param Plugin\Plugin $plugin
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function registerPlugin(Plugin\Plugin $plugin) {
        $this->pluginManager->registerPlugin($plugin);
    }

    /**
     * @param string $name
     * @return void
     */
    public function removePlugin($name) {
        $this->pluginManager->removePlugin($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasPlugin($name) {
        return $this->pluginManager->hasPlugin($name);
    }

    /**
     * @param string $name
     * @return Plugin\Plugin
     * @throws Exception\NotFoundException
     */
    public function getPlugin($name) {
        return $this->pluginManager->getPlugin($name);
    }

    /**
     * @return Plugin\Plugin[]
     */
    public function getPlugins() {
        return $this->pluginManager->getPlugins();
    }

}
