<?php

declare(strict_types=1);

/**
 * A Plugin that registers listeners with the Emitter.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Plugin;

use Cspray\Labrador\AsyncEvent\Emitter;

interface EventAwarePlugin extends Plugin {

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param Emitter $emitter
     * @return void
     */
    public function registerEventListeners(Emitter $emitter) : void;

}
