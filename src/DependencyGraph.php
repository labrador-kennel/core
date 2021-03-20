<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Auryn\Injector;
use Auryn\InjectorException;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Cspray\Labrador\Exception\DependencyInjectionException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\PluginManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * An object that wires together an object graph to ensure that Labrador works out-of-the-box.
 *
 * While you could replicate this within your own DependencyGraph object it is recommended that to define Labrador
 * dependencies on your Injector you use this implementation. You can then further define dependencies on the Injector
 * returned to customize or add dependencies your Application might need that is not suitable for a Plugin.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 * @deprecated Planned for removal in 4.0
 * @see CoreApplicationObjectGraph
 */
final class DependencyGraph {

    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Returns an Injector that will allow the creation of the dependencies that Labrador needs out-of-the-box.
     *
     * @param Injector|null $injector
     * @return Injector
     * @throws DependencyInjectionException
     */
    public function wireObjectGraph(Injector $injector = null) : Injector {
        try {
            $injector = $injector ?? new Injector();

            $injector->share(EventEmitter::class);
            $injector->alias(EventEmitter::class, AmpEventEmitter::class);

            $injector->share(Pluggable::class);
            $injector->alias(Pluggable::class, PluginManager::class);
            $injector->define(PluginManager::class, [
                ':injector' => $injector
            ]);

            $injector->share(Engine::class);
            $injector->alias(Engine::class, AmpEngine::class);

            $logger = $this->logger;
            $injector->share($logger);
            $injector->alias(LoggerInterface::class, get_class($logger));
            $injector->prepare(LoggerAwareInterface::class, function(LoggerAwareInterface $aware) use($logger) {
                $aware->setLogger($logger);
            });

            return $injector;
        } catch (InjectorException $injectorException) {
            /** @var DependencyInjectionException $exception */
            $exception = Exceptions::createException(Exceptions::DEPENDENCY_GRAPH_INJECTION_ERR, $injectorException);
            throw $exception;
        }
    }
}
