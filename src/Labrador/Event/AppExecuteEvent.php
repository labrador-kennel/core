<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Labrador\Engine;

class AppExecuteEvent {

    use EngineEventTrait;

    public function __construct(Engine $engine) {
        $this->setEngine($engine);
    }



} 
