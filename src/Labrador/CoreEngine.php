<?php

/**
 *
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Labrador\Event\AppExecuteEvent;
use Labrador\Event\ExceptionThrownEvent;
use Labrador\Event\PluginBootEvent;
use Labrador\Event\PluginCleanupEvent;
use Labrador\Plugin\Plugin;
use Labrador\Plugin\PluginManager;
use Evenement\EventEmitterInterface;

class CoreEngine implements Engine {

    private $emitter;
    private $pluginManager;

    public function __construct(EventEmitterInterface $emitter, PluginManager $pluginManager) {
        $this->emitter = $emitter;
        $this->pluginManager = $pluginManager;
    }

    public function getName() {
        return 'Labrador Core';
    }

    public function getVersion() {
        return '0.1.0-alpha';
    }

    public function onPluginBoot(callable $cb) {
        $this->emitter->on(self::PLUGIN_BOOT_EVENT, $cb);
    }

    public function onAppExecute(callable $cb) {
        $this->emitter->on(self::APP_EXECUTE_EVENT, $cb);
    }

    public function onPluginCleanup(callable $cb) {
        $this->emitter->on(self::PLUGIN_CLEANUP_EVENT, $cb);
    }

    public function run() {
        try {
            $this->emitter->emit(self::PLUGIN_BOOT_EVENT, [new PluginBootEvent($this)]);
            $this->emitter->emit(self::APP_EXECUTE_EVENT, [new AppExecuteEvent($this)]);
        } catch (\Exception $exception) {
            $this->emitter->emit(self::EXCEPTION_THROWN_EVENT, [new ExceptionThrownEvent($this, $exception)]);
        } finally {
            $this->emitter->emit(self::PLUGIN_CLEANUP_EVENT, [new PluginCleanupEvent($this)]);
        }
    }

    public function registerPlugin(Plugin $plugin) {
        $this->pluginManager->registerPlugin($plugin);
    }

    public function removePlugin($name) {
        $this->pluginManager->removePlugin($name);
    }

    public function hasPlugin($name) {
        return $this->pluginManager->hasPlugin($name);
    }

    public function getPlugin($name) {
        return $this->pluginManager->getPlugin($name);
    }

    public function getPlugins() {
        return $this->pluginManager->getPlugins();
    }

}
