<?php

/**
 * An event triggered once before the application is executed; this should give
 * every Plugin registered to the Engine the ability to carry out any procedures
 * it may need to execute.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Labrador\Engine;

class PluginBootEvent {

    use EngineEventTrait;

    /**
     * @param Engine $engine
     */
    public function __construct(Engine $engine) {
        $this->setEngine($engine);
    }

} 
