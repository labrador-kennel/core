<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Labrador\Engine;
use Exception;

class ExceptionThrownEvent {

    use EngineEventTrait;

    private $exception;

    public function __construct(Engine $engine, Exception $exception) {
        $this->setEngine($engine);
        $this->exception = $exception;
    }

    public function getException() {
        return $this->exception;
    }

} 
