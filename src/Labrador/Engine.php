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
use Labrador\Plugin\Pluggable;
use Labrador\Plugin\Plugin;
use Labrador\Plugin\PluginManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Engine implements Pluggable {

    const PLUGIN_BOOT_EVENT = 'labrador.plugin_boot';
    const APP_EXECUTE_EVENT = 'labrador.application_execute';
    const PLUGIN_CLEANUP_EVENT = 'labrador.plugin_cleanup';
    const EXCEPTION_THROWN_EVENT = 'labrador.exception_thrown';

    const CATCH_EXCEPTIONS = 1;
    const THROW_EXCEPTIONS = 2;

    private $eventDispatcher;
    private $pluginManager;

    public function __construct(EventDispatcherInterface $eventDispatcher, PluginManager $pluginManager) {
        $this->eventDispatcher = $eventDispatcher;
        $this->pluginManager = $pluginManager;
    }

    public function run() {
        try {
            $this->eventDispatcher->dispatch(self::PLUGIN_BOOT_EVENT, new PluginBootEvent($this));
            $this->eventDispatcher->dispatch(self::APP_EXECUTE_EVENT, new AppExecuteEvent());
        } catch (\Exception $exception) {
            $this->eventDispatcher->dispatch(self::EXCEPTION_THROWN_EVENT, new ExceptionThrownEvent($this, $exception));
        } finally {
            $this->eventDispatcher->dispatch(self::PLUGIN_CLEANUP_EVENT, new PluginCleanupEvent());
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
