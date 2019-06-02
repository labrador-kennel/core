<?php

declare(strict_types = 1);

/**
 * An interface for a plugin that needs to perform some action when the Engine boots up.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Plugin;

use Amp\Promise;

interface BootablePlugin extends Plugin {

    /**
     * Return a callable that will be invoked using Auryn's Injector::execute API.
     *
     * By invoking the callable with your application's Injector you can typehint your callable with any service that
     * has been wired by your object graph OR if this object is also a PluginDependentPlugin the services provided by
     * those dependent plugins. Your callable will also be invoked on the event loop and can yield or return a promise
     * and will work as expected.
     *
     * It is very important that your callable only typehints against objects known to be wired in your container. If
     * you typehint a scalar value or a type that cannot be instantiated by the Injector an exception will be thrown.
     *
     * @return callable
     */
    public function boot() : Promise;
}
