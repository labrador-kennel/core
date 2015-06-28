<?php

declare(strict_types=1);

/**
 * An event triggered when an exception is thrown when Engine::run is invoked.
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Event;

use Exception;
use Labrador\Engine;

class ExceptionThrownEvent extends Event {

    private $exception;

    /**
     * @param Exception $exception
     */
    public function __construct(Exception $exception) {
        parent::__construct(Engine::EXCEPTION_THROWN_EVENT);
        $this->exception = $exception;
    }

    /**
     * @return Exception
     */
    public function getException() : Exception {
        return $this->exception;
    }

}
