<?php

/**
 * An event triggered when an exception is thrown when Engine::run is invoked.
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

    /**
     * @param Engine $engine
     * @param Exception $exception
     */
    public function __construct(Engine $engine, Exception $exception) {
        $this->setEngine($engine);
        $this->exception = $exception;
    }

    /**
     * @return Exception
     */
    public function getException() {
        return $this->exception;
    }

} 
