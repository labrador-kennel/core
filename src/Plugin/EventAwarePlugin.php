<?php declare(strict_types=1);


namespace Cspray\Labrador\Plugin;

use Cspray\Labrador\AsyncEvent\Emitter;

/**
 * A Plugin that is capable of interacting with the Application's Emitter to register event listeners when this Plugin
 * is loaded and to remove those listeners when the Plugin is removed from the Pluggable.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE in source root
 */
interface EventAwarePlugin extends Plugin {

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param Emitter $emitter
     * @return void
     */
    public function registerEventListeners(Emitter $emitter) : void;

    /**
     * Remove any event listeners that were registered when registerEventListeners was invoked.
     *
     * @param Emitter $emitter
     * @return void
     */
    public function removeEventListeners(Emitter $emitter) : void;

}
