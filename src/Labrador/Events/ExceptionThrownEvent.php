<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Symfony\Component\HttpFoundation\Request;
use Exception as PhpException;
use Symfony\Component\HttpFoundation\Response;

class ExceptionThrownEvent extends LabradorEvent {

    private $exception;
    private $response;

    function __construct(Request $request, Response $response, PhpException $exception) {
        parent::__construct($request);
        $this->exception = $exception;
        $this->response = $response;
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
