<?php declare(strict_types = 1);

namespace Cspray\Labrador\Plugin;

use Amp\Promise;

/**
 * A Plugin capable of executing some possibly asynchronous operation when the Plugin loading process is started.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE in source root
 */
interface BootablePlugin extends Plugin {

    /**
     * Return a Promise that resolves when the booting process is finished.
     *
     * @return void
     */
    public function boot() : void;
}
