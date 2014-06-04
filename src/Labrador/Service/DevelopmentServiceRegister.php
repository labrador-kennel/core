<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Service;

use Auryn\Injector;
use Labrador\Service\Register;
use Labrador\Development\Runtimes;

class DevelopmentServiceRegister implements Register {

    function register(Injector $injector) {
        $requestTime = $_SERVER['REQUEST_TIME_FLOAT'];
        $injector->share(Runtimes::class);
        $injector->define(Runtimes::class, [
            ':requestTime' => $requestTime
        ]);
    }

} 
