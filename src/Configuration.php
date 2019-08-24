<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Ds\Set;

/**
 * Implementations define the data for the ConfiguredApplicationInvoker, the out-of-the-box way your Application
 * instance is executed.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
interface Configuration {

    /**
     * Return the name of the log that Monolog will use to identify log messages from this Application.
     *
     * @return string
     */
    public function getLogName() : string;

    /**
     * Return a path that can be used as a resource stream to write log messages to.
     *
     * @return string
     */
    public function getLogPath() : string;

    /**
     * Return a path in which the file returns a callable that accepts a single Configuration instance and returns an
     * Injector.
     *
     * @return string
     */
    public function getInjectorProviderPath() : string;

    /**
     * Return an array of fully qualified class names for the Plugins that should be added to your Application.
     *
     * @return string[]
     */
    public function getPlugins() : Set;
}
