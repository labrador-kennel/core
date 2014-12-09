<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Labrador\Engine;
use Symfony\Component\EventDispatcher\Event;

class ExceptionThrownEvent extends Event {

    private $engine;
    private $exception;

    public function __construct(Engine $engine, \Exception $exception) {
        $this->engine = $engine;
        $this->exception = $exception;
    }

    public function getEngine() {
        return $this->engine;
    }

    public function getException() {
        return $this->exception;
    }

} 
