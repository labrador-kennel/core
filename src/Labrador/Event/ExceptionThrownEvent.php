<?php

/**
 * Event triggered when an exception is caught by Labrador\Application.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Event;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Exception as PhpException;

class ExceptionThrownEvent extends LabradorEvent {

    private $exception;

    /**
     * @param RequestStack $requestStack
     * @param Response $response
     * @param \Exception $exception
     */
    function __construct(RequestStack $requestStack, Response $response, PhpException $exception) {
        parent::__construct($requestStack);
        $this->exception = $exception;
        $this->setResponse($response);
    }

    /**
     * @return \Exception
     */
    function getException() {
        return $this->exception;
    }

} 
