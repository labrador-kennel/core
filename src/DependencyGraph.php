<?php

declare(strict_types=1);

/**
 * A convenience object to easily get started with Labrador; creates and returns an
 * Auryn\Injector instance with Labrador's services already configured.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Auryn\Injector;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\AmpEmitter;
use Cspray\Labrador\Plugin\PluginManager;

class DependencyGraph {

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

        return $injector;
    }
}
