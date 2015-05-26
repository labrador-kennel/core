<?php

/**
 * A convenience object to easily get started with Labrador; creates and returns an
 * Auryn\Injector instance with Labrador's services already configured.
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Labrador\Plugin\PluginManager;
use Auryn\Injector;
use Evenement\EventEmitterInterface;

class Services {

    public function createInjector() {
        $injector = new Injector();

        $injector->share($injector);
        $injector->share(Event\HaltableEventEmitter::class);
        $injector->alias(EventEmitterInterface::class, Event\HaltableEventEmitter::class);

        $injector->share(PluginManager::class);
        $injector->share(CoreEngine::class);
        $injector->alias(Engine::class, CoreEngine::class);

        return $injector;
    }

}