<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Event\ExceptionThrownEvent;
use Auryn\Injector;
use Whoops\Run;

function bootstrap() : Injector {
    $run = (new Run())->register();

    $injector = (new Services())->wireObjectGraph();
    $injector->share($run);

    return $injector;
}