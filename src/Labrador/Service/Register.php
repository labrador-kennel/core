<?php

/**
 * Interface for implementations that will add a set of services to a Provider.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Service;

use Auryn\Injector;

interface Register {

    /**
     * @param Injector $injector
     * @return mixed
     */
    function register(Injector $injector);

}
