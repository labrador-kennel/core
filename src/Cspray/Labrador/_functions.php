<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador;

use Auryn\Injector;
use function Cspray\Labrador\injector;

/**
 * @return CoreEngine
 * @throws \Auryn\InjectionException
 */
function engine() : CoreEngine {
    static $engine;
    if (!$engine) {
        $engine = injector()->make(Engine::class);
    }

    return $engine;
}

function injector() : Injector {
    static $injector;
    if (!$injector) {
        $injector = (new Services)->createInjector();
    }

    return $injector;
}