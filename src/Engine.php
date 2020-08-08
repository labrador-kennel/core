<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\AsyncEvent\EventEmitter;
use Psr\Log\LoggerAwareInterface;

/**
 * An interface that effectively encapsulates the running of an Application on a Loop.
 *
 * Engine implementations are ideally responsible for 3 things:
 *
 * 1. Ensure that appropriate events are triggered when an engine boots up before the Application starts and another
 * event after the Application has finished executing.
 * 2. Load the Application's Plugins then invoke Application::start().
 * 3. Ensure that the Application has a chance to handle uncaught exceptions
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
     * Return the state of the Engine; this SHOULD BE one of the defined Engine constants that ends in _STATE.
     *
     * @return EngineState
     */
    public function getState() : EngineState;

    /**
     * Return the event emitter that will emit the events for this Engine.
     *
     * @return EventEmitter
     */
    public function getEmitter() : EventEmitter;

    /**
     * Ensure that the Application has its plugins loaded and then execute its business logic.
     *
     * @param Application $application
     * @return void
     */
    public function run(Application $application) : void;
}
