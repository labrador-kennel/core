<?php

declare(strict_types=1);

/**
 * An interface that represents primary execution logic for a Labrador powered application.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Plugin\Pluggable;
use League\Event\EmitterInterface;

interface Engine extends Pluggable {

    // These are the bare MINIMUM amount of events that an engine should trigger
    // An Engine MAY trigger more events but at least these should be
    const ENGINE_BOOTUP_EVENT = 'labrador.engine_bootup';
    const APP_EXECUTE_EVENT = 'labrador.app_execute';
    const APP_CLEANUP_EVENT = 'labrador.app_cleanup';
    const EXCEPTION_THROWN_EVENT = 'labrador.exception_thrown';

    /**
     * Return the event emitter that will emit the events for this Engine.
     *
     * @return EmitterInterface
     */
    public function getEmitter() : EmitterInterface;

    /**
     * Perform whatever actions are necessary for this implementation.
     *
     * @return mixed
     */
    public function run();

}
