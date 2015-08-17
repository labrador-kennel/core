<?php

declare(strict_types = 1);

/**
 * Triggered once after Labrador has bootstrapped itself and the Engine
 * has started running, but before any Plugins are loaded or your app
 * takes over.
 *
 * For Labrador we hook into this event being triggered to run any
 * environment initializers you've registered with the Environment and
 * to load any Plugins for the engine.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Event;

use Cspray\Labrador\Engine;
use Telluris\Environment;
use League\Event\Event;

class EnvironmentInitializeEvent extends Event {

    private $environment;

    /**
     * @param Environment $environment
     */
    public function __construct(Environment $environment) {
        parent::__construct(Engine::ENVIRONMENT_INITIALIZE_EVENT);
        $this->environment = $environment;
    }

    /**
     * @return Environment
     */
    public function getEnvironment() : Environment {
        return $this->environment;
    }

}