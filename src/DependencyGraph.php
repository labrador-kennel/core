<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Auryn\Injector;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\AmpEmitter;
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
 */
final class DependencyGraph {

    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Returns an Injector that will allow the creation of the dependencies that Labrador needs out-of-the-box.
     *
     * The current mapping that this Injector provides:
     *
     * - Cspray\Labrador\AsyncEvent\Emitter -> Cspray\Labrador\AsyncEvent\AmpEmitter
     * - Cspray\Labrador\Plugin\PluginManager -> ''
     * - Cspray\Labrador\Engine -> Cspray\Labrador\AmpEngine
     *
     * @param Injector|null $injector
     * @return Injector
     * @throws \Auryn\ConfigException
     * @throws \Auryn\InjectionException
     */
    public function wireObjectGraph(Injector $injector = null) : Injector {
        $injector = $injector ?? new Injector();

        $injector->share(AmpEmitter::class);
        $injector->alias(Emitter::class, AmpEmitter::class);

        $injector->share(PluginManager::class);
        $injector->define(PluginManager::class, [
            ':injector' => $injector
        ]);

        $injector->share(AmpEngine::class);
        $injector->alias(Engine::class, AmpEngine::class);

        $injector->share($this->logger);
        $injector->alias(LoggerInterface::class, get_class($this->logger));
        $injector->prepare(LoggerAwareInterface::class, function(LoggerAwareInterface $aware) {
            $aware->setLogger($this->logger);
        });

        return $injector;
    }
}
