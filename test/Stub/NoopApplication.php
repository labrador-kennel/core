<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Amp\Promise;
use Amp\Success;
use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\Plugin\Pluggable;
use Throwable;

class NoopApplication implements Application {

    /**
     * Perform whatever logic or operations your application requires; return a Promise that resolves when you app is
     * finished running.
     *
     * This method should avoid throwing an exception and instead fail the Promise with the Exception that caused the
     * application to crash.
     *
     * @return Promise
     */
    public function execute(): Promise {
        return new Success();
    }

    public function exceptionHandler(Throwable $throwable) : void {
        // TODO: Implement exceptionHandler() method.
    }
}
