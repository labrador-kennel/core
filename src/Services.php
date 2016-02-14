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
use League\Event\{EmitterInterface, Emitter};

class Services {

    public function wireObjectGraph(Injector $injector = null) : Injector {
        $injector = $injector ?? new Injector();

        $injector->share($injector);
        $injector->share(Emitter::class);
        $injector->alias(EmitterInterface::class, Emitter::class);

        $injector->share(PluginManager::class);
        $injector->share(CoreEngine::class);
        $injector->alias(Engine::class, CoreEngine::class);

        return $injector;
    }

}
