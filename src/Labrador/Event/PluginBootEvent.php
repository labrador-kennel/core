<?php

/**
 *
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Labrador\Engine;
use Symfony\Component\EventDispatcher\Event;

class PluginBootEvent extends Event {

    private $engine;

    public function __construct(Engine $engine) {
        $this->engine;
    }

    public function getEngine() {
        return $this->engine;
    }

} 
