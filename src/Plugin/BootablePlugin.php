<?php declare(strict_types = 1);

namespace Cspray\Labrador\Plugin;

use Amp\Promise;

/**
 * A Plugin capable of executing some possibly asynchronous operation when the Pluggable this Plugin is attached to
 * calls Pluggable::loadPlugins.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE in source root
 */
interface BootablePlugin extends Plugin {

    /**
     * Return a Promise that resolves when the booting process is finished.
     *
     * @return Promise<void>
     */
    public function boot() : Promise;
}
