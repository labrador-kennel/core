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
     * Perform whatever logic or operations your application requires; return a Promise that resolves when you app is
     * finished running.
     *
     * This method should avoid throwing an exception and instead fail the Promise with the Exception that caused the
     * application to crash.
     *
     * @return Promise<void>
     */
    public function execute() : Promise;

    /**
     * Handle an exception being thrown in your application; if you can gracefully handle the exception the app will
     * continue to run otherwise rethrow the exception to cause the application to shutdown.
     *
     * @param Throwable $throwable
     * @return void
     */
    public function exceptionHandler(Throwable $throwable) : void;
}
