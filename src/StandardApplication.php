<?php declare(strict_types=1);


namespace Cspray\Labrador;

use Cspray\Labrador\AsyncEvent\Emitter;
use Auryn\Injector;
use Throwable;

abstract class StandardApplication implements Application {

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot() : void {
        // noop, override in your Application to do something at boot time
    }

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param Emitter $emitter
     * @return void
     */
    public function registerEventListeners(Emitter $emitter) : void {
        // noop, override in your Application to respond to emitted events
    }

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return iterable
     */
    public function dependsOn(): iterable {
        // noop, override in your Application to require Plugins be registered
        return [];
    }

    /**
     * Register any services that the Plugin provides.
     *
     * @param Injector $injector
     * @return void
     */
    public function registerServices(Injector $injector) : void {
        // noop, override in your Application to wire your object graph to the Auryn container.
    }

    public function exceptionHandler(Throwable $throwable) : void {
        throw $throwable;
    }
}
