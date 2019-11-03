<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Amp\Promise;
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
     * Resolve the Promise either when the Application has naturally reached a stopping point OR when the
     * Application::stop is called. For some types of long-running Applications it is expected that this Promise will
     * not resolve unless Application::stop is explicitly invoked.
     *
     * If start() is called successive times without allowing the Promises to resolve as the method is invoked an
     * InvalidStateException MUST be thrown. It is not expected nor will it be supported that an Application may start
     * after it has been started and before it has been stopped.
     *
     * This method should avoid throwing an exception and instead fail the Promise with the Exception that caused the
     * application to crash.
     *
     * @return Promise<void>
     */
    public function start() : Promise;

    /**
     * Force the Application to stop running; the Promise returned allows the Application to potentially gracefully
     * handle any remaining tasks.
     *
     * The Promise returned from Application::start() MUST resolve before this Promise resolves or your Application may
     * enter a state where it cannot be stopped without forcefully killing the process.
     *
     * @return Promise
     */
    public function stop() : Promise;

    /**
     * Return the state in which the Application is currently in.
     *
     * @return ApplicationState
     */
    public function getState() : ApplicationState;

    /**
     * Handle an exception being thrown in your application; if you can gracefully handle the exception the app will
     * continue to run otherwise rethrow the exception to cause the application to shutdown.
     *
     * @param Throwable $throwable
     * @return void
     */
    public function handleException(Throwable $throwable) : void;
}
