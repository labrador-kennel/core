<?php

declare(strict_types=1);

/**
 * A Plugin that provides a service, as an object, to the Auryn IoC container powering
 * the Labrador application.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Plugin;

use Auryn\Injector;

interface InjectorAwarePlugin extends Plugin {

    /**
     * Register any services that the Plugin provides.
     *
     * @param Injector $injector
     * @return void
     */
    public function wireObjectGraph(Injector $injector) : void;
}
