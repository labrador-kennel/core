<?php

declare(strict_types=1);

/**
 * An interface that represents primary execution logic for a Labrador powered application.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\Plugin\Pluggable;

interface Engine {

    // These are the bare MINIMUM amount of events that an engine should trigger
    // An Engine MAY trigger more events but at least these
    const ENGINE_BOOTUP_EVENT = 'labrador.engine_bootup';
    const APP_CLEANUP_EVENT = 'labrador.app_cleanup';

    /**
     * Return the event emitter that will emit the events for this Engine.
     *
     * @return Emitter
     */
    public function getEmitter() : Emitter;

    /**
     * Execute the application's primary logic.
     *
     * @param Application $application
     * @return void
     */
    public function run(Application $application) : void;
}
