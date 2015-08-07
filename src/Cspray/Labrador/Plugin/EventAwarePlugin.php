<?php

declare(strict_types=1);

/**
 * A Plugin that responds to a triggered event.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Plugin;

use Evenement\EventEmitterInterface;

interface EventAwarePlugin extends Plugin {

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param EventEmitterInterface $emitter
     * @return void
     */
    public function registerEventListeners(EventEmitterInterface $emitter);

}