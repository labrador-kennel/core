<?php declare(strict_types=1);

namespace Cspray\Labrador\Plugin;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\Exception\DependencyInjectionException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;

use Auryn\Injector;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use function Amp\async;

/**
 * The default Pluggable implementation that manages the lifecycle of Plugins for all out-of-the-box Applications.
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
final class PluginManager implements Pluggable, LoggerAwareInterface {

    use LoggerAwareTrait;

    private EventEmitter $emitter;
    private Injector $injector;

    private array $plugins = [];
    private array $loadHandlers = [];
    private array $removeHandlers = [];
    private array $loading = [];

    private bool $pluginsLoaded = false;

    /**
     * Constructs the PluginManager with dependencies required to be provided to certain Plugin types.
     *
     * There are 2 primary reasons for asking for the Injector in this class; the first is that we are required to
     * construct Plugins from a string and constructing objects in a fashion that all known dependencies are provided
     * is a natural responsibility of the Injector. The second is that the InjectorAwarePlugin requires that an Injector
     * be provided during the plugin loading process.
     *
     * @param Injector $injector
     * @param EventEmitter $emitter
     */
    public function __construct(Injector $injector, EventEmitter $emitter) {
        $this->injector = $injector;
        $this->emitter = $emitter;
    }

    /**
     * @inheritDoc
     */
    public function registerPluginLoadHandler(string $pluginType, callable $pluginHandler, ...$arguments): void {
        $this->loadHandlers[$pluginType][] = [$pluginHandler, $arguments];
    }

    /**
     * @inheritDoc
     */
    public function registerPlugin(string $plugin) : void {
        /** @var InvalidArgumentException|InvalidStateException $exception */
        if ($exception = $this->guardRegisterPluginIsValid($plugin)) {
            throw $exception;
        }
        $this->plugins[$plugin] = null;
        $this->logger->info(sprintf('Registered Plugin "%s".', $plugin));
    }

    /**
     * @param string $plugin
     * @return Exception|null
     */
    private function guardRegisterPluginIsValid(string $plugin) : ?Exception {
        if ($this->hasPluginBeenRegistered($plugin)) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_HAS_BEEN_REGISTERED,
                null,
                $plugin
            );
        }

        if ($this->pluginsLoaded) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_REGISTER_PLUGIN_POSTLOAD,
                null
            );
        }

        $implementedTypes = class_implements($plugin);
        if (!in_array(Plugin::class, $implementedTypes)) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_REGISTER_NOT_PLUGIN_TYPE,
                null,
                $plugin
            );
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function loadPlugins() : void {
        $registeredPlugins = $this->getRegisteredPlugins();
        if (empty($registeredPlugins)) {
            $this->pluginsLoaded = true;
            $this->logger->info('No Plugins were registered.');
            return;
        }
        $this->logger->info(sprintf(
            'Initiating Plugin loading. Loading %d registered Plugins, not including dependencies.',
            count($this->plugins)
        ));
        foreach ($registeredPlugins as $pluginName) {
            $this->loadPlugin($pluginName);
        }
        $this->logger->info(sprintf(
            'Finished loading %d Plugins, including dependencies.',
            count($this->plugins)
        ));
        $this->pluginsLoaded = true;
    }

    /**
     * @inheritDoc
     */
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
        $this->logger->info(sprintf('Removed Plugin "%s".', $name));
        unset($this->plugins[$name]);
    }

    /**
     * @inheritDoc
     */
    public function registerPluginRemoveHandler(string $pluginType, callable $pluginHandler, ...$arguments) : void {
        $this->removeHandlers[$pluginType][] = [$pluginHandler, $arguments];
    }

    /**
     * @inheritDoc
     */
    public function hasPluginBeenRegistered(string $name) : bool {
        return array_key_exists($name, $this->plugins);
    }

    /**
     * @inheritDoc
     */
    public function havePluginsLoaded() : bool {
        return $this->pluginsLoaded;
    }

    /**
     * @inheritDoc
     */
    public function getLoadedPlugin(string $name) : Plugin {
        if (!$this->hasPluginBeenRegistered($name)) {
            /** @var InvalidArgumentException $exception */
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_PLUGIN_NOT_FOUND,
                null,
                $name
            );
            throw $exception;
        }

        if (!isset($this->plugins[$name])) {
            /** @var InvalidStateException $exception */
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD,
                null
            );
            throw $exception;
        }

        return $this->plugins[$name];
    }

    /**
     * @inheritDoc
     */
    public function getLoadedPlugins() : array {
        if (!$this->havePluginsLoaded()) {
            /** @var InvalidStateException $exception */
            $exception = Exceptions::createException(
                Exceptions::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD,
                null
            );
            throw $exception;
        }
        return array_values($this->plugins);
    }

    /**
     * @inheritDoc
     */
    public function getRegisteredPlugins() : array {
        return array_keys($this->plugins);
    }

    private function loadPlugin(string $pluginName) : void {
        if ($this->notLoaded($pluginName)) {
            $this->startLoading($pluginName);

            $this->handlePluginDependencies($pluginName);

            $plugin = $this->injector->make($pluginName);

            $this->handlePluginServices($plugin);
            $this->handlePluginEvents($plugin);
            $this->handleCustomPluginHandlers($plugin);
            $this->bootPlugin($plugin);
            $this->finishLoading($plugin);
        }
    }

    private function notLoaded(string $plugin) {
        return !isset($this->plugins[$plugin]);
    }

    private function startLoading(string $plugin) {
        $this->logger->info(sprintf('Starting to load %s.', $plugin));
        $this->loading[] = $plugin;
    }

    private function finishLoading(Plugin $plugin) {
        $this->logger->info(sprintf('Finished loading %s.', get_class($plugin)));
        $name = get_class($plugin);
        $this->loading = array_diff($this->loading, [$name]);
        $this->plugins[$name] = $plugin;
    }

    private function isLoading(string $plugin) {
        return in_array($plugin, $this->loading);
    }

    private function handlePluginDependencies(string $plugin) : void {
        $implementedTypes = class_implements($plugin);
        if (in_array(PluginDependentPlugin::class, $implementedTypes)) {
            foreach (call_user_func([$plugin, 'dependsOn']) as $reqPluginName) {
                if ($exception = $this->guardLoadingValidPluginDependency($plugin, $reqPluginName)) {
                    throw $exception;
                }

                $this->logger->info(sprintf(
                    'Loading dependencies for %s.',
                    $plugin
                ));
                $this->loadPlugin($reqPluginName);
                $this->logger->info(sprintf(
                    'Finished loading dependencies for %s.',
                    $plugin
                ));
            }
        }
    }

    /**
     * @param string $plugin
     * @param string $reqPluginName
     * @return Exception|null
     */
    private function guardLoadingValidPluginDependency(string $plugin, string $reqPluginName) : ?Exception {
        if ($this->isLoading($reqPluginName)) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_CIRCULAR_DEPENDENCY,
                null,
                $plugin,
                $reqPluginName
            );
        }

        $dependencyTypes = class_implements($reqPluginName);
        if (!in_array(Plugin::class, $dependencyTypes)) {
            return Exceptions::createException(
                Exceptions::PLUGIN_ERR_DEPENDENCY_NOT_PLUGIN_TYPE,
                null,
                $plugin,
                $reqPluginName
            );
        }

        return null;
    }

    /**
     * @param Plugin $plugin
     * @throws DependencyInjectionException
     */
    private function handlePluginServices(Plugin $plugin) {
        if ($plugin instanceof InjectorAwarePlugin) {
            $this->logger->info(sprintf(
                'Wiring object graph for %s.',
                get_class($plugin)
            ));
            $plugin->wireObjectGraph($this->injector);
        }
    }

    private function handlePluginEvents(Plugin $plugin) {
        if ($plugin instanceof EventAwarePlugin) {
            $this->logger->info(sprintf(
                'Registering event listeners for %s.',
                get_class($plugin)
            ));
            $plugin->registerEventListeners($this->emitter);
        }
    }

    private function handleCustomPluginHandlers(Plugin $plugin) : void {
        foreach ($this->loadHandlers as $type => $pluginHandlers) {
            if ($plugin instanceof $type) {
                $pluginName = get_class($plugin);
                $this->logger->info(sprintf(
                    'Found %d custom handlers for %s.',
                    count($pluginHandlers),
                    $pluginName
                ));
                foreach ($pluginHandlers as list($pluginHandler, $pluginHandlerArgs)) {
                    async($pluginHandler, $plugin, ...$pluginHandlerArgs)->await();
                }
                $this->logger->info(sprintf(
                    'Finished loading custom handlers for %s.',
                    $pluginName
                ));
            }
        }
    }

    private function bootPlugin(Plugin $plugin) : void {
        if ($plugin instanceof BootablePlugin) {
            $pluginName = get_class($plugin);
            $this->logger->info(sprintf(
                'Starting %s boot procedure.',
                $pluginName
            ));
            $plugin->boot();
            $this->logger->info(sprintf(
                'Finished %s boot procedure.',
                $pluginName
            ));
        }
    }
}
