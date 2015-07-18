<?php

declare(strict_types=1);

/**
 * A Plugin that provides a service, as an object, to the Auryn IoC container powering
 * the Labrador application.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Plugin;

use Auryn\Injector;

interface ServiceAwarePlugin extends Plugin {

    /**
     * Register any services that the Plugin provides.
     *
     * @param Injector $injector
     * @return void
     */
    public function registerServices(Injector $injector);

} 
