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
 * An abstract Application that handles the vast majority of boilerplate necessary to implement the Application
 * interface leaving only the primary business logic to your implementations.
 *
 * This method delegates all of the Pluggable responsibilities to an instance that must be injected at construction.
 * Although you may pass any Pluggable type to this instance you almost assuredly want to inject the PluginManager class
 * as it is the de facto implementation for loading Plugins correctly. If you use the provided DependencyGraph object,
 * which you definitely should, then any Pluggable type hint in constructors will be resolved to the PluginManager.
 *
 * If you do not use our DependencyGraph it is your responsibility to create your application with the appropriate
 * dependency:
 *
 * $app = $yourInjector->make(YourApplication::class, ['pluggable' => \Cspray\Labrador\Plugin\PluginManager::class]);
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
    public function start() : Promise {
        if (!$this->state->equals(ApplicationState::Stopped())) {
            throw Exceptions::createException(Exceptions::APP_ERR_MULTIPLE_START_CALLS);
        }

        // We use the deferred object instead of simply returning the result of Amp\call because there are two ways for
        // the start Promise to be resolved. Either implicitly when the Application stops running or explicitly by
        // invoking the Application::stop method.
        $this->deferred = new Deferred();

        $this->state = ApplicationState::Started();
        $this->doStart()->onResolve(function($err) {
            // This ensures we properly handle the case where doStart may return a Promise that resolves immediately.
            // If we were to yield an instantaneous Promise we would call resolveDeferred(), which sets $deferred to
            // null, before we actually returned the Promise for the $deferred required by Application::start(). By
            // resolving the Promise for when this is done executing on the next tick of the event loop we ensure
            // there's actually something to return for the start() method.
            Loop::defer(function() use($err) {
                $this->resolveDeferred($err);
            });
        });

        return $this->deferred->promise();
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function getState() : ApplicationState {
        return $this->state;
    }

    abstract protected function doStart() : Promise;

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
