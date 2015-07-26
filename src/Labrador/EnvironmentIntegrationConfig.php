<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Labrador;

use Telluris\Config\Storage;
use Telluris\Config\NullStorage;
use Telluris\Environment;

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