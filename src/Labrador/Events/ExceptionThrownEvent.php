<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Symfony\Component\HttpFoundation\RequestStack;
use Exception as PhpException;
use Symfony\Component\HttpFoundation\Response;

class ExceptionThrownEvent extends LabradorEvent {

    private $exception;

    function __construct(RequestStack $requestStack, Response $response, PhpException $exception) {
        parent::__construct($requestStack);
        $this->exception = $exception;
        $this->setResponse($response);
    }

    function getException() {
        return $this->exception;
    }

} 
