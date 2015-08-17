<?php

declare(strict_types = 1);

/**
 * An object that dictates how Labrador integrates with Telluris.
 *
 * This implementation and the integration with Telluris has been designed in such a way that it is
 * an opt-in figure. Nothing about Labrador is actually dependent upon the library, it is simply
 * provided as a means to manage your environment. If you don't want to use the functionality you
 * can safely ignore it and should have no impacts on your application.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador;

use Cspray\Telluris\Config\Storage;
use Cspray\Telluris\Config\NullStorage;
use Cspray\Telluris\Environment;

class EnvironmentIntegrationConfig {

    private $storage;
    private $env;
    private $runInitializers;

    public function __construct(Storage $storage = null, string $env = Environment::DEVELOPMENT, bool $runInitializers = true) {
        $this->storage = $storage ?? new NullStorage();
        $this->env = $env;
        $this->runInitializers = $runInitializers;
    }

    public function getStorage() : Storage {
        return $this->storage;
    }

    public function getEnv() : string {
        return $this->env;
    }

    public function runInitializers() : bool {
        return $this->runInitializers;
    }

}