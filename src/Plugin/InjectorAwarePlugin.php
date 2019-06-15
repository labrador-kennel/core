<?php declare(strict_types=1);

namespace Cspray\Labrador\Plugin;

use Auryn\Injector;

/**
 * Plugin responsible for wiring an object graph to a given Injector.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE in source root
 */
interface InjectorAwarePlugin extends Plugin {

    /**
     * Register an object graph onto the passed Injector.
     *
     * You should refrain from calling Injector::make in this method and should ideally be defining parameters, sharing
     * objects that are intended to be single instances, and configuring any factory methods onto your Injector. Making
     * an object here is likely a sign that your implementation is doing too much.
     *
     *
     * @param Injector $injector
     * @return void
     */
    public function wireObjectGraph(Injector $injector) : void;
}
