<?php

declare(strict_types = 1);

/**
 * An interface for a plugin that needs to perform some action when the Engine boots up.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Plugin;

interface BootablePlugin extends Plugin {

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot() : void;

}