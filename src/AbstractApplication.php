<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Amp\Promise;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
use Psr\Log\LoggerAwareTrait;
use Throwable;

/**
 * An abstract Application implementation that handles all Pluggable and LoggerAwareInterface responsibilities leaving
 * implementing classes only responsible for providing an execute and exceptionHandler methods.
 *
 * This method delegates all of the Pluggable responsibilities to an instance that must be injected at construction.
 * Although you may pass any Pluggable type to this instance you almost assuredly want to inject the PluginManager class
 * as it is the de facto implementation for loading Plugins correctly. If you use the provided DependencyGraph object,
 * which you definitely should, and your Configuration has a valid application class configured this task has been
 * taken care of for you and any instance you create with the Injector will have the appropriate Pluggable. If you
 * do not use our DependencyGraph OR do not have an application class appropriately Configured it is your responsibility
 * to create your application with the appropriate dependency:
 *
 * $app = $injector->make(YourApplication::class, ['pluggable' => \Cspray\Labrador\Plugin\PluginManager::class]);
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
abstract class AbstractApplication implements Application {

    use LoggerAwareTrait;

    private $pluggable;

    public function __construct(Pluggable $pluggable) {
        $this->pluggable = $pluggable;
    }

    /**
     * This implementation does nothing by default, logging of the exception itself for Labrador's purposes are handled
     * within the Engine and there's nothing more we can do.
     *
     * If your Application needs to have custom exception handling when an exception bubbles up to the event loop you
     * should override this method.
     *
     * @param Throwable $throwable
     */
    public function exceptionHandler(Throwable $throwable) : void {
        // noop
    }

    public function registerPluginLoadHandler(string $pluginType, callable $pluginHandler, ...$arguments) : void {
        $this->pluggable->registerPluginLoadHandler($pluginType, $pluginHandler, ...$arguments);
    }

    public function registerPluginRemoveHandler(string $pluginType, callable $pluginHandler, ...$arguments) : void {
        $this->pluggable->registerPluginRemoveHandler($pluginType, $pluginHandler, ...$arguments);
    }

    public function registerPlugin(string $plugin) : void {
        $this->pluggable->registerPlugin($plugin);
    }

    public function loadPlugins() : Promise {
        return $this->pluggable->loadPlugins();
    }

    public function removePlugin(string $pluginType) : void {
        $this->pluggable->removePlugin($pluginType);
    }

    public function hasPluginBeenRegistered(string $pluginType) : bool {
        return $this->pluggable->hasPluginBeenRegistered($pluginType);
    }

    public function havePluginsLoaded() : bool {
        return $this->pluggable->havePluginsLoaded();
    }

    public function getLoadedPlugin(string $pluginType) : Plugin {
        return $this->pluggable->getLoadedPlugin($pluginType);
    }

    public function getLoadedPlugins() : array {
        return $this->pluggable->getLoadedPlugins();
    }

    public function getRegisteredPlugins() : array {
        return $this->pluggable->getRegisteredPlugins();
    }
}