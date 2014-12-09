<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

use Auryn\Injector;

interface ServiceAwarePlugin extends Plugin {

    /**
     * @param Injector $injector
     */
    public function registerServices(Injector $injector);

} 
