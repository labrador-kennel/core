<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Amp\Loop;
use Amp\Deferred;
use Amp\Promise;
use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\Plugin\Pluggable;
use Throwable;

class CallbackApplication implements Application {

    private $callback;

    public function __construct(callable $callback) {
        $this->callback= $callback;
    }

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
        $deferred = new Deferred();
        Loop::defer(function() use($deferred) {
            $callback = $this->callback;
            $promise = $callback();
            if ($promise instanceof \Generator) {
                $promise = yield from $promise;
            }
            $promise->onResolve(function($error, $result) use($deferred) {
                if ($error) {
                    $deferred->fail($error);
                } else {
                    $deferred->resolve($result);
                }
            });
        });
        return $deferred->promise();
    }

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot() : void {
        // noop
    }

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param Emitter $emitter
     * @return void
     */
    public function registerEventListeners(Emitter $emitter) : void {
        // noop
    }

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return array
     */
    public function dependsOn(): iterable {
        return [];
    }

    /**
     * Register any services that the Plugin provides.
     *
     * @param Injector $injector
     * @return void
     */
    public function registerServices(Injector $injector) : void {
        // noop
    }

    public function exceptionHandler(Throwable $throwable) : void {
        // TODO: Implement exceptionHandler() method.
    }
}
