<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Amp\DeferredFuture;
use Amp\Future;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
use Psr\Log\LoggerAwareTrait;
use Revolt\EventLoop;
use Throwable;

/**
 * An abstract Application that handles the vast majority of boilerplate necessary for the Application interface leaving
 * only the primary business logic to your implementations.
 *
 * This method delegates all of the Pluggable responsibilities to an instance that must be injected at construction.
 * Although you may pass any Pluggable type to this instance you almost assuredly want to inject the PluginManager class
 * as it is the de facto implementation for loading Plugins correctly. If you ensure that your ApplicationObjectGraph
 * properly extends from `CoreApplicationObjectGraph` this will be taken care of for you out of the box. If you do not
 * use the `CoreApplicationObjectGraph` it is your responsibility for providing the appropriate Pluggable when
 * constructing children of this object.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
abstract class AbstractApplication implements Application {

    use LoggerAwareTrait;

    private Pluggable $pluggable;
    private ?DeferredFuture $deferred;
    private ApplicationState $state;

    /**
     * @param Pluggable $pluggable
     */
    public function __construct(Pluggable $pluggable) {
        $this->pluggable = $pluggable;
        $this->state = ApplicationState::Stopped();
    }

    /**
     * @inheritDoc
     */
    final public function start() : Future {
        if (!$this->state->equals(ApplicationState::Stopped())) {
            throw Exceptions::createException(Exceptions::APP_ERR_MULTIPLE_START_CALLS);
        }

        $this->deferred = new DeferredFuture();

        $this->setState(ApplicationState::Started());
        EventLoop::defer(function() {
            try {
                $this->doStart()
                    ->map(fn() => $this->resolveDeferred())
                    ->await();
            } catch (Throwable $throwable) {
                $this->resolveDeferred($throwable);
            }
        });

        return $this->deferred->getFuture();
    }

    /**
     * @inheritDoc
     */
    public function stop() : Future {
        $this->resolveDeferred();
        return Future::complete();
    }

    private function resolveDeferred(Throwable $throwable = null) : void {
        if (isset($throwable)) {
            $this->setState(ApplicationState::Crashed());
            $this->deferred->error($throwable);
        } else {
            $this->setState(ApplicationState::Stopped());
            $this->deferred->complete();
        }
        $this->deferred = null;
    }

    /**
     * @inheritDoc
     */
    final public function getState() : ApplicationState {
        return $this->state;
    }

    final protected function setState(ApplicationState $state) : void {
        $this->state = $state;
    }

    abstract protected function doStart() : Future;

    /**
     * @inheritDoc
     */
    public function handleException(Throwable $throwable) : void {
        $this->logException($throwable);
    }

    /**
     * Ensures that the Exception and any previous Exceptions have appropriate information logged as a critical message.
     *
     * @param Throwable $throwable
     * @param array $context
     */
    protected function logException(Throwable $throwable, array $context = []) : void {
        $this->logger->critical($throwable->getMessage(), array_merge([], $context, [
            'class' => get_class($throwable),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'code' => $throwable->getCode(),
            'stack_trace' => $throwable->getTrace(),
            'previous' => $this->marshalPreviousExceptions($throwable)
        ]));
    }

    private function marshalPreviousExceptions(Throwable $original) : ?array {
        $doMarshaling = function(Throwable $previous = null) use(&$doMarshaling) {
            if (is_null($previous)) {
                return null;
            }

            return [
                'class' => get_class($previous)  ,
                'message' => $previous->getMessage(),
                'code' => $previous->getCode(),
                'file' => $previous->getFile(),
                'line' => $previous->getLine(),
                'stack_trace' => $previous->getTrace(),
                'previous' => $doMarshaling($previous->getPrevious())
            ];
        };

        return $doMarshaling($original->getPrevious());
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

    public function loadPlugins() : void {
        $this->pluggable->loadPlugins();
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
