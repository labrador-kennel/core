<?php

declare(strict_types=1);

/**
 * An event triggered when an exception is thrown when Engine::run is invoked.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Event;

use Cspray\Labrador\Engine;
use Exception;

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
