<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Amp\Deferred;
use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\Exception\InvalidStateException;
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
 * taken care of for you and any instance you create with the Injector will have the appropriate Pluggable.
 *
 * If you do not use our DependencyGraph OR do not have an application class appropriately Configured it is your
 * responsibility to create your application with the appropriate dependency:
 *
 * $app = $injector->make(YourApplication::class, ['pluggable' => \Cspray\Labrador\Plugin\PluginManager::class]);
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
abstract class AbstractApplication implements Application {

    use LoggerAwareTrait;

    /**
     * @var Pluggable
     */
    private $pluggable;

    /**
     * @var Deferred
     */
    private $deferred;

    /**
     * @var ApplicationState
     */
    private $state;

    public function __construct(Pluggable $pluggable) {
        $this->pluggable = $pluggable;
        $this->state = ApplicationState::Stopped();
    }

    public function start() : Promise {
        if (!$this->state->equals(ApplicationState::Stopped())) {
            $msg = 'Application must be in a Stopped state to start but it\'s current state is %s';
            throw new InvalidStateException(sprintf($msg, $this->state->toString()));
        }
        $this->deferred = new Deferred();

        $this->state = ApplicationState::Started();
        $this->doStart()->onResolve(function($err) {
            // This ensures we properly handle the case where doStart may return a Promise that resolves immediately
            // In such a case we would call resolveDeferred(), which sets $deferred to null, before we actually
            // returned the Promise for the $deferred required by Application::start(). By resolving the Promise for
            // when this is done executing on the next tick of the event loop we ensure there's actually something to
            // return for the start() method.
            Loop::defer(function() use($err) {
                $this->resolveDeferred($err);
            });
        });

        return $this->deferred->promise();
    }

    public function stop() : Promise {
        $this->resolveDeferred();
        return new Success();
    }

    private function resolveDeferred(Throwable $throwable = null) : void {
        if (isset($throwable)) {
            $this->state = ApplicationState::Crashed();
            $this->deferred->fail($throwable);
        } else {
            $this->state = ApplicationState::Stopped();
            $this->deferred->resolve();
        }
        $this->deferred = null;
    }

    public function getState() : ApplicationState {
        return $this->state;
    }

    abstract protected function doStart() : Promise;

    /**
     * This implementation does nothing by default, logging of the exception itself for Labrador's purposes are handled
     * within the Engine and there's nothing more we can do.
     *
     * If your Application needs to have custom exception handling when an exception bubbles up to the event loop you
     * should override this method.
     *
     * @param Throwable $throwable
     */
    public function handleException(Throwable $throwable) : void {
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
