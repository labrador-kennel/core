<?php

/**
 * An event triggered once when your application should execute its
 * processing logic and deliver a result to the user.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Labrador\Engine;

class AppExecuteEvent {

    use EngineEventTrait;

    /**
     * @param Engine $engine
     */
    public function __construct(Engine $engine) {
        $this->setEngine($engine);
    }

} 
