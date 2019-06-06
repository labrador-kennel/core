<?php

declare(strict_types = 1);

/**
 * An interface for a plugin that needs to perform some action when the Pluggable::loadPlugins method is invoked.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Plugin;

use Amp\Promise;

interface BootablePlugin extends Plugin {

    /**
     * Return a Promise that resolves when the booting process is finished.
     *
     * @return Promise<void>
     */
    public function boot() : Promise;
}
