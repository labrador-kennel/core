<?php

/**
 * A trait to ensure all events triggered by Labrador have access to
 * the Engine that triggered the event.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Labrador\Engine;

trait EngineEventTrait {

    private $engine;

    public function getEngine() {
        return $this->engine;
    }

    protected function setEngine(Engine $engine) {
        $this->engine = $engine;
    }

} 
