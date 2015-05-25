<?php

/**
 * An event triggered when an exception is thrown when Engine::run is invoked.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Exception;

class ExceptionThrownEvent {

    private $exception;

    /**
     * @param Exception $exception
     */
    public function __construct(Exception $exception) {
        $this->exception = $exception;
    }

    /**
     * @return Exception
     */
    public function getException() {
        return $this->exception;
    }

} 
