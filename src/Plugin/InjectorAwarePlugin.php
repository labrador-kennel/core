<?php declare(strict_types=1);

namespace Cspray\Labrador\Plugin;

use Auryn\Injector;
use Cspray\Labrador\Exception\DependencyInjectionException;

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
     * If an error is encountered that would cause an Auryn\InjectorException to be thrown it MUST be caught and
     * rethrown as a DependencyInjectionException. We should endeavor to prohibit 3rd party exceptions from leaking out
     * of Labrador components.
     *
     * @param Injector $injector
     * @return void
     * @throws DependencyInjectionException
     */
    public function wireObjectGraph(Injector $injector) : void;
}
