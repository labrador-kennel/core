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
    private $response;

    function __construct(RequestStack $requestStack, Response $response, PhpException $exception) {
        parent::__construct($requestStack);
        $this->response = $response;
        $this->exception = $exception;
    }

    function getException() {
        return $this->exception;
    }

    function getResponse() {
        return $this->response;
    }

    function setResponse(Response $response) {
        $this->response = $response;
    }

} 
