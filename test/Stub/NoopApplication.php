<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Amp\Promise;
use Amp\Success;
use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class NoopApplication implements Application {

    use LoggerAwareTrait;

    /**
     * Perform whatever logic or operations your application requires; return a Promise that resolves when you app is
     * finished running.
     *
     * This method should avoid throwing an exception and instead fail the Promise with the Exception that caused the
     * application to crash.
     *
     * @return Promise
     */
    public function execute(): Promise {
        return new Success();
    }

    public function exceptionHandler(Throwable $throwable) : void {
        // TODO: Implement exceptionHandler() method.
    }

    /**
     * Register a handler for a custom Plugin type to be invoked when loadPlugins is invoked.
     *
     * @param string $pluginType The fully qualified class name for the Plugin that should have the handler invoked
     * @param callable $pluginHandler function(YourPluginType $plugin, ...$arguments) : Promise|Generator|void {}
     * @param mixed ...$arguments Any arguments that you may pass to the handler, passed AFTER the Plugin
     */
    public function registerPluginLoadHandler(string $pluginType, callable $pluginHandler, ...$arguments): void {
        // TODO: Implement registerPluginLoadHandler() method.
    }

    /**
     * Register a handler for a custom Plugin type to be invoked when removePlugin is called with a type that matches
     * the $pluginType.
     *
     * If plugins have not yet been loaded when the target Plugin is removed this callback will not be invoked.
     *
     * @param string $pluginType The fully qualified class name for the Plugin that should have the handler invoked
     * @param callable $pluginHandler function(YourPluginType plugin, ...$arguments) : Promise|Generator|void {}
     * @param mixed ...$arguments Any arguments that you may pass to the handler, passed AFTER the Plugin
     */
    public function registerPluginRemoveHandler(string $pluginType, callable $pluginHandler, ...$arguments): void {
        // TODO: Implement registerPluginRemoveHandler() method.
    }

    /**
     * Register a fully qualified class name that implements the Plugin interface that should be instantiated and loaded
     * when loadPlugins is invoked.
     *
     * If a Plugin is attempted to be registered AFTER loadPlugins is invoked an IllegalStateException SHOULD be thrown
     * as all registration must happen prior to loading to simplify the process.
     *
     * If a Plugin is attempted to be registered that does not implement the Plugin interace an IllegalArgumentException
     * MUST be thrown as all registered types must implement the minimum interface.
     *
     * @param string $plugin The fully qualified class name of the Plugin to register
     * @return void
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function registerPlugin(string $plugin): void {
        // TODO: Implement registerPlugin() method.
    }

    /**
     * Go through the loading and booting process for all Plugins that have been registered to this Pluggable.
     *
     * If Plugin A is being loaded and depends on Plugin B but Plugin B also depends on Plugin A you MUST throw a
     * CircularDependencyException as the registered Plugins are invalid and cannot be properly loaded.
     *
     * If a PluginDependentPlugin depends on a class that does not implement the Plugin interface you MUST throw an
     * InvalidStateException as a registered Plugin's state does not allow the loading of its dependencies.
     *
     * @return Promise<void> Will resolve when all Plugins have completed the loading process
     * @throws CircularDependencyException
     */
    public function loadPlugins(): Promise {
        return new Success();
    }

    /**
     * Removes the Plugin from the list of both registered and loaded plugins, assuming loadPlugins has been invoked.
     *
     * This method will also cause the Plugin to have any potential remove handlers invoked if the loading process has
     * completed upon time of removal.
     *
     * @param string $pluginType The fully qualified class name of the Plugin to remove
     * @return void
     */
    public function removePlugin(string $pluginType): void {
        // TODO: Implement removePlugin() method.
    }

    /**
     * Determine if a Plugin has been registered or not.
     *
     * Please note that this MAY NOT return only values that have been passed to registerPlugin. All dependencies of
     * PluginDependentPlugins MUST BE implicitly registered as part of their loading process.
     *
     * @param string $pluginType The fully qualified class name of the Plugin to check for registry
     * @return boolean True or false for whether the given plugin has been registered
     */
    public function hasPluginBeenRegistered(string $pluginType): bool {
        // TODO: Implement hasPluginBeenRegistered() method.
    }

    /**
     * @return bool True or false for whether all registered Plugins, and their dependencies, have fully loaded
     */
    public function havePluginsLoaded(): bool {
        // TODO: Implement havePluginsLoaded() method.
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
     * @param string $pluginType The fully qualified class name of the Plugin to retrieve
     * @return Plugin The Plugin instance used for the loading process
     * @throws NotFoundException
     * @throws InvalidStateException
     */
    public function getLoadedPlugin(string $pluginType): Plugin {
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
    public function getLoadedPlugins(): array {
        // TODO: Implement getLoadedPlugins() method.
    }

    /**
     * An array of Plugin types that have been registered with this Pluggable, either through the registerPlugin method
     * or implicitly during the loading of PluginDependentPlugins.
     *
     * @return string[]
     */
    public function getRegisteredPlugins(): array {
        // TODO: Implement getRegisteredPlugins() method.
    }
}
