<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Auryn\Injector;

/**
 * Define the critical dependencies that are required by your Application to be instantiated and started properly.
 *
 * While the ApplicationObjectGraph and InjectorAwarePlugin share very similar responsibilities they are distinctly
 * different in how they are expected to be used and what they are meant to specifically accomplish.
 *
 * @package Cspray\Labrador
 */
interface ApplicationObjectGraph {

    /**
     * Return an Injector that is completely configured for your Application.
     *
     * @return Injector
     */
    public function wireObjectGraph() : Injector;

}