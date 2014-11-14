<?php

/**
 * Event triggered after the successful controller for a given request has been
 * invoked.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Event;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class AfterControllerEvent extends LabradorEvent {

    private $controller;

    /**
     * @param RequestStack $requestStack
     * @param Response $response
     * @param callable $controller
     */
    function __construct(RequestStack $requestStack, Response $response, callable $controller) {
        parent::__construct($requestStack);
        $this->setResponse($response);
    }

    /**
     * Returns the controller that was invoked for the given request.
     *
     * @return callable
     */
    function getController() {
        return $this->controller;
    }

} 
