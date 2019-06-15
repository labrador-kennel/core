<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\AsyncEvent\Emitter;

/**
 * An interface that effectively encapsulates the running of an Application on an event loop.
 *
 * Engine implementations are ideally responsible for 3 things:
 *
 * 1. Ensuring that appropriate events are triggered when an engine boots up before the Application starts and another
 * event after the Application has finished executing.
 * 2. Ensure that an Application has loaded its plugins and then to execute it.
 * 3. Ensure that if an Exception is thrown during any of the above the Application's exception handler has a chance to
 * respond to it.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
interface Engine {

    // These are the bare MINIMUM amount of events that an engine should trigger
    // An Engine MAY trigger more events but at least these
    const ENGINE_BOOTUP_EVENT = 'labrador.engine_bootup';
    const ENGINE_SHUTDOWN_EVENT = 'labrador.engine_shutdown';

    /**
     * Return the event emitter that will emit the events for this Engine.
     *
     * @return Emitter
     */
    public function getEmitter() : Emitter;

    /**
     * Ensure that the Application has its plugins loaded and then execute its business logic.
     *
     * @param Application $application
     * @return void
     */
    public function run(Application $application) : void;
}
