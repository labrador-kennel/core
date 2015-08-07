<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Event;

use Cspray\Labrador\Engine;
use Telluris\Environment;

class EnvironmentInitializeEvent extends Event {

    private $environment;

    public function __construct(Environment $environment) {
        parent::__construct(Engine::ENVIRONMENT_INITIALIZE_EVENT);
        $this->environment = $environment;
    }

    public function getEnvironment() : Environment {
        return $this->environment;
    }

}