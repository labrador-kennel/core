<?php declare(strict_types=1);

namespace Cspray\Labrador\Plugin;

use Amp\Promise;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\AsyncEvent\Emitter;
use Auryn\Injector;

use function Amp\call;

/**
 * De facto Pluggable implementation that manages the lifecycle of Plugins for all out-of-the-box Applications.
 *
 * It is HIGHLY recommended that if you implement your own Pluggable interface that you delegate the actual
 * responsibilities for handling the lifecycle of the Plugin to an instance of this object; it is well tested and
 * implements the Plugin loading process in a known order that other Plugins may be reliant upon.
 *
 * Another important aspect of using this class over implementing the Pluggable methods in your own code is that this
 * object helps abstract away the fact that we must ask for an Injector as a constructor dependency. By keeping that
 * Injector dependency outside of your application and consuming code there's less opportunity for your Injector to
 * be turned into a Service Locator.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE in source root
 */
final class PluginManager implements Pluggable {

    private $emitter;
    private $injector;

    private $plugins = [];
    private $loadHandlers = [];
    private $removeHandlers = [];
    private $loading = [];

    private $pluginsBooted = false;

    public function __construct(Injector $injector, Emitter $emitter) {
        $this->injector = $injector;
        $this->emitter = $emitter;
    }

    public function registerPluginLoadHandler(string $pluginType, callable $pluginHandler, ...$arguments): void {
        $this->loadHandlers[$pluginType][] = [$pluginHandler, $arguments];
    }

    public function registerPlugin(string $plugin) : void {
        if ($this->hasPluginBeenRegistered($plugin)) {
            $msg = "A Plugin with name $plugin has already been registered and may not be registered again.";
            throw new InvalidArgumentException($msg);
        }

        if ($this->pluginsBooted) {
            $msg = 'Plugins have already been loaded and you MUST NOT register plugins after this has taken place.';
            throw new InvalidStateException($msg);
        }

        $implementedTypes = class_implements($plugin);
        if (!in_array(Plugin::class, $implementedTypes)) {
            $msg = 'Attempted to register a Plugin that does not implement the ' . Plugin::class . ' interface';
            throw new InvalidArgumentException($msg);
        }

        $this->plugins[$plugin] = null;
    }

    public function loadPlugins(): Promise {
        return call(function() {
            foreach ($this->getRegisteredPlugins() as $pluginName) {
                yield $this->loadPlugin($pluginName);
            }
            $this->pluginsBooted = true;
        });
    }

    public function removePlugin(string $name) : void {
        if (isset($this->plugins[$name])) {
            $plugin = $this->plugins[$name];
            if ($plugin instanceof EventAwarePlugin) {
                $plugin->removeEventListeners($this->emitter);
            }

            foreach ($this->removeHandlers as $pluginType => $handlers) {
                if ($plugin instanceof $pluginType) {
                    foreach ($handlers as list($handler, $args)) {
                        $handler($plugin, ...$args);
                    }
                }
            }
        }
        unset($this->plugins[$name]);
    }

    public function registerPluginRemoveHandler(string $pluginType, callable $pluginHandler, ...$arguments) : void {
        $this->removeHandlers[$pluginType][] = [$pluginHandler, $arguments];
    }

    public function hasPluginBeenRegistered(string $name) : bool {
        return array_key_exists($name, $this->plugins);
    }

    public function havePluginsLoaded() : bool {
        return $this->pluginsBooted;
    }

    public function getLoadedPlugin(string $name) : Plugin {
        if (!array_key_exists($name, $this->plugins)) {
            $msg = 'Could not find a registered plugin named "%s"';
            throw new NotFoundException(sprintf($msg, $name));
        }

        if (!isset($this->plugins[$name])) {
            $msg = 'A loaded Plugin may only be gathered after ' . Pluggable::class . '::loadPlugins invoked';
            throw new InvalidStateException($msg);
        }

        return $this->plugins[$name];
    }

    public function getLoadedPlugins() : array {
        if (!$this->havePluginsLoaded()) {
            $msg = 'Loaded plugins may only be gathered after ' . Pluggable::class . '::loadPlugins invoked';
            throw new InvalidStateException($msg);
        }
        return array_values($this->plugins);
    }

    public function getRegisteredPlugins() : array {
        return array_keys($this->plugins);
    }

    private function loadPlugin(string $pluginName) : Promise {
        return call(function() use($pluginName) {
            if ($this->notLoaded($pluginName)) {
                $this->startLoading($pluginName);
                yield $this->handlePluginDependencies($pluginName);

                $plugin = $this->injector->make($pluginName);

                $this->handlePluginServices($plugin);
                $this->handlePluginEvents($plugin);
                yield $this->handleCustomPluginHandlers($plugin);
                yield $this->bootPlugin($plugin);
                $this->finishLoading($plugin);
            }
        });
    }

    private function notLoaded(string $plugin) {
        return !isset($this->plugins[$plugin]);
    }

    private function startLoading(string $plugin) {
        $this->loading[] = $plugin;
    }

    private function finishLoading(Plugin $plugin) {
        $name = get_class($plugin);
        $this->loading = array_diff($this->loading, [$name]);
        $this->plugins[$name] = $plugin;
    }

    private function isLoading(string $plugin) {
        return in_array($plugin, $this->loading);
    }

    private function handlePluginDependencies(string $plugin) : Promise {
        return call(function() use($plugin) {
            $implementedTypes = class_implements($plugin);
            if (in_array(PluginDependentPlugin::class, $implementedTypes)) {
                foreach (call_user_func([$plugin, 'dependsOn']) as $reqPluginName) {
                    if ($this->isLoading($reqPluginName)) {
                        $msg = 'A circular dependency was found with %s requiring %s.';
                        $msg = sprintf($msg, $plugin, $reqPluginName);
                        throw new CircularDependencyException($msg);
                    }

                    $dependencyTypes = class_implements($reqPluginName);
                    if (!in_array(Plugin::class, $dependencyTypes)) {
                        $msg = 'A Plugin, ' . $plugin . ', depends on a ';
                        $msg .= 'type, ' . $reqPluginName . ', that does not implement ' . Plugin::class;
                        throw new InvalidStateException($msg);
                    }


                    yield $this->loadPlugin($reqPluginName);
                }
            }
        });
    }

    private function handlePluginServices(Plugin $plugin) {
        if ($plugin instanceof InjectorAwarePlugin) {
            $plugin->wireObjectGraph($this->injector);
        }
    }

    private function handlePluginEvents(Plugin $plugin) {
        if ($plugin instanceof EventAwarePlugin) {
            $plugin->registerEventListeners($this->emitter);
        }
    }

    private function handleCustomPluginHandlers(Plugin $plugin) : Promise {
        return call(function() use($plugin) {
            foreach ($this->loadHandlers as $type => $pluginHandlers) {
                if ($plugin instanceof $type) {
                    foreach ($pluginHandlers as list($pluginHandler, $pluginHandlerArgs)) {
                        yield call($pluginHandler, $plugin, ...$pluginHandlerArgs);
                    }
                }
            }
        });
    }

    private function bootPlugin(Plugin $plugin) : Promise {
        return call(function() use($plugin) {
            if ($plugin instanceof BootablePlugin) {
                yield $plugin->boot();
            }
        });
    }
}
