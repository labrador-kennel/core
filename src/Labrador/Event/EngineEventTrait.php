<?php

/**
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
        $this->engine;
    }

} 
