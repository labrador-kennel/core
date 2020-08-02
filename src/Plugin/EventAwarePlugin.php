<?php declare(strict_types=1);


namespace Cspray\Labrador\Plugin;

use Cspray\Labrador\AsyncEvent\EventEmitter;

/**
 * A Plugin that is capable of interacting with the Application's EventEmitter.
 *
 * Register event listeners during the Plugin loading process. Unregister event listeners if the Plugin is explicitly
 * removed from the Pluggable.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE in source root
 */
interface EventAwarePlugin extends Plugin {

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param EventEmitter $emitter
     * @return void
     */
    public function registerEventListeners(EventEmitter $emitter) : void;

    /**
     * Remove any event listeners that were registered when registerEventListeners was invoked.
     *
     * @param EventEmitter $emitter
     * @return void
     */
    public function removeEventListeners(EventEmitter $emitter) : void;
}
