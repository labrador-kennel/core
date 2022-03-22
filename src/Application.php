<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Amp\Future;
use Cspray\Labrador\Plugin\Pluggable;
use Psr\Log\LoggerAwareInterface;
use Throwable;

/**
 * An interface that represents the encapsulation of your business logic and the Plugins your Application requires to
 * run correctly.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
interface Application extends Pluggable, LoggerAwareInterface {

    /**
     * Start running your Application, performing whatever logic is necessary for the given implementation.
     *
     * Resolve the Future either when the Application has naturally reached a stopping point OR when the
     * Application::stop is called. For some types of long-running Applications it is expected that this Promise will
     * not resolve unless Application::stop is explicitly invoked.
     *
     * If start() is called successive times without allowing the Future to resolve completely an InvalidStateException
     * MUST be thrown. Start an already started or crashed Application SHOULD NOT be supported. Whether the Application
     * is in a valid state can be determined with getState().
     *
     * @return Future<void>
     */
    public function start() : Future;

    /**
     * Force the Application to stop running; the Future returned allows the Application to potentially gracefully
     * handle any remaining tasks.
     *
     * The Future returned from Application::start() MUST resolve before this Promise resolves or your Application may
     * enter a state where it cannot be stopped without forcefully killing the process.
     *
     * @return Future<void>
     */
    public function stop() : Future;

    /**
     * Return the state in which the Application is currently in.
     *
     * @return ApplicationState
     */
    public function getState() : ApplicationState;

    /**
     * Handle an uncaught exception being thrown in your application.
     *
     * @param Throwable $throwable
     * @return void
     */
    public function handleException(Throwable $throwable) : void;
}
