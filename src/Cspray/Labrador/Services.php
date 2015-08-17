<?php

declare(strict_types=1);

/**
 * A convenience object to easily get started with Labrador; creates and returns an
 * Auryn\Injector instance with Labrador's services already configured.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Event\EnvironmentInitializeEvent;
use Auryn\Injector;
use League\Event\EmitterInterface;
use League\Event\Emitter;
use Telluris\Config\Storage;
use Telluris\Environment;

class Services {

    private $envInitConfig;

    public function __construct(EnvironmentIntegrationConfig $envInitConfig = null) {
        $this->envInitConfig = $envInitConfig ?? new EnvironmentIntegrationConfig();
    }

    public function createInjector() : Injector {
        $injector = new Injector();

        $injector->share($injector);
        $injector->share(Emitter::class);
        $injector->alias(EmitterInterface::class, Emitter::class);

        $injector->share(PluginManager::class);
        $injector->share(CoreEngine::class);
        $injector->alias(Engine::class, CoreEngine::class);

        $injector->share(Environment::class);
        $injector->define(Environment::class, [':env' => $this->envInitConfig->getEnv()]);

        $envStorage = $this->envInitConfig->getStorage();
        $injector->share($envStorage);
        $injector->alias(Storage::class, get_class($envStorage));

        if ($this->envInitConfig->runInitializers()) {
            /** @var EmitterInterface $emitter */
            $emitter = $injector->make(EmitterInterface::class);
            $emitter->addListener(Engine::ENVIRONMENT_INITIALIZE_EVENT, function(EnvironmentInitializeEvent $event) {
                $event->getEnvironment()->runInitializers();
            }, EmitterInterface::P_HIGH);
        }

        return $injector;
    }

}
