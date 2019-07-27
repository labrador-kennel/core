<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\AsyncEvent\Emitter;
use Psr\Log\LoggerAwareInterface;

/**
 * An interface that effectively encapsulates the running of an Application on an event loop.
 *
 * Engine implementations are ideally responsible for 3 things:
 *
 * 1. Ensure that appropriate events are triggered when an engine boots up before the Application starts and another
 * event after the Application has finished executing.
 * 2. Load the Application's Plugins then invoke Application::execute.
 * 3. Ensure that if an Exception is thrown during any of the above the Application's exception handler has a chance to
 * respond to it.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
interface Engine extends LoggerAwareInterface {

    /**
     * An event that is triggered one time whenever the Engine::run method is invoked before any of your Application
     * code begins executing
     */
    const START_UP_EVENT = 'labrador.engine_start_up';

    /**
     * An event that is triggered one time whenever the Engine::run method is invoked after your Application code is
     * done executing.
     */
    const SHUT_DOWN_EVENT = 'labrador.engine_shut_down';

    /**
     * A state that represents the Engine is not running; this could be returned before and after the Engine::run
     * method is invoked.
     */
    const IDLE_STATE = 'idle';

    /**
     * A state that represents the Engine::run method has been invoked and the Engine is currently processing your
     * Application code, either by loading Plugins or invoking Application::execute.
     */
    const RUNNING_STATE = 'running';

    /**
     * A state that represents when the
     */
    const CRASHED_STATE = 'crashed';

    /**
     * Return the state of the Engine; this SHOULD BE one of the defined Engine constants that ends in _STATE.
     *
     * @return string
     */
    public function getState() : string;

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
