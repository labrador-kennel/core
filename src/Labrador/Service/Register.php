<?php

/**
 * Interface for implementations that will add a set of services to an Auryn\Injector.
 * 
 * @license See LICENSE in source root
 *
 * @see https://github.com/rdlowrey/Auryn
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
