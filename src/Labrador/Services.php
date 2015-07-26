<?php

declare(strict_types=1);

/**
 * A convenience object to easily get started with Labrador; creates and returns an
 * Auryn\Injector instance with Labrador's services already configured.
 *
 * @license See LICENSE in source root
 */

namespace Labrador;

use Labrador\Event\HaltableEventEmitter;
use Auryn\Injector;
use Evenement\EventEmitterInterface;

class Services {

    public function createInjector() : Injector {
        $injector = new Injector();

        $injector->share($injector);
        $injector->share(HaltableEventEmitter::class);
        $injector->alias(EventEmitterInterface::class, HaltableEventEmitter::class);

        $injector->share(PluginManager::class);
        $injector->share(CoreEngine::class);
        $injector->alias(Engine::class, CoreEngine::class);

        return $injector;
    }

}
