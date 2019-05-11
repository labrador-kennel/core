<?php declare(strict_types=1);

/**
 * Pluggable interface
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Plugin;

use Amp\Promise;
use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exception\NotFoundException;

/**
 * An interface for objects that have Plugins associated to them and are responsible for manging the lifecycle of the
 * Plugin for as long as it remains attached to the given Pluggable.
 *
 * The primary responsibility of a Pluggable is to ensure that the various hooks for a given Plugin type are invoked
 * properly. The responsibility of invoking each Plugin type's hooks is left to a plugin handler; the Plugin types that
 * exist within Labrador\Core\Plugin SHOULD have their plugin handlers be an implicit part of the Pluggable
 * implementation. If your application requires custom Plugin hooks you should implement those with application specific
 * code and taking advantage of registerPluginHandler.
 *
 * 1. Any dependencies should have this process completed if the registered Plugin is a PluginDependentPlugin. The
 * process should detect for circular references and throw an exception if one is encountered.
 * 2. If the registered Plugin is a InjectorAwarePlugin the Injector should be provided so that the object graph for the
 * Plugin can be wired properly.
 * 3. If the registered Plugin is an EventAwarePlugin the Emitter should be provided so that any listeners can be
 * registered.
 * 4. If the registered Plugin has any custom Plugin handlers each one should be invoked in the order it was registered.
 * 5. Finally, if the registered Plugin is a BootablePlugin the callback from boot() should be invoked with the
 * Injector::execute method.
 *
 * @package Cspray\Labrador\Plugin
 */
interface Pluggable {

    /**
     * Register a handler for a custom Plugin type that is not natively supported by Labrador.
     *
     * For more information about how custom handlers interact with Plugins during the initialization process please
     * review the Plugins documentation.
     *
     * @param string $pluginType
     * @param callable $pluginHandler
     * @param mixed ...$arguments
     */
    public function registerPluginHandler(string $pluginType, callable $pluginHandler, ...$arguments) : void;

    /**
     * Store the Plugin to prepare it for loading when Pluggable::loadPlugins() is called; typically in Labrador
     * powered Applications this happens for you when the Engine::ENGINE_BOOTUP_EVENT is triggered.
     *
     * If the Plugin passed has already been registered throw an InvalidArgumentException as a Plugin MUST only be
     * registered one time.
     *
     * If the Plugin is registered after the Plugglable::loadPlugins() event is called throw an InvalidStateException as
     * all Plugins must be registered before they are loaded. This is necessary because loading Plugins requires the
     * event loop be running, something we don't necessarily want to enforce simply for Plugin registration. It also is
     * indicative of a poorly designed application as, specifically, the BootablePlugin::boot method is intended to
     * run at initialization and could be designed in a way to be longer running than you would want once your app is
     * serving clients.
     *
     * @param Plugin $plugin
     * @return void
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function registerPlugin(Plugin $plugin) : void;

    /**
     * Go through the loading and booting process for all Plugins that have been registered to this Pluggable.
     *
     * This is a distinct operation separate from registering a Plugin so that you can ensure all PluginDependentPlugin
     * dependencies are provided and to express the intent that the loadPlugins process is asynchronous and needs to be
     * executed on an event loop.
     *
     * If Plugin A is being loaded and depends on Plugin B but Plugin B also depends on Plugin A you MUST throw a
     * CircularDependencyException as the registered Plugins are invalid and cannot be properly loaded.
     *
     * @return Promise
     * @throws CircularDependencyException
     */
    public function loadPlugins() : Promise;

    /**
     * @param string $name
     */
    public function removePlugin(string $name) : void;

    /**
     * @param string $name
     * @return boolean
     */
    public function hasPlugin(string $name) : bool;

    /**
     * @param string $name
     * @return Plugin
     * @throws NotFoundException
     */
    public function getPlugin(string $name) : Plugin;

    /**
     * An array of Plugin objects associated to the given Pluggable.
     *
     * @return Plugin[]
     */
    public function getPlugins() : iterable;
}
