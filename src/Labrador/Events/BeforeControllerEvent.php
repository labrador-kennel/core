<?php

/**
 * Event triggered when a route was successfully found for a request.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class BeforeControllerEvent extends LabradorEvent {

    private $controllerCb;
    private $response;

    function __construct(RequestStack $requestStack, callable $controllerCb) {
        parent::__construct($requestStack);
        $this->controllerCb = $controllerCb;
    }

    function getController() {
        return $this->controllerCb;
    }

    function setController(callable $controllerCb) {
        $this->controllerCb = $controllerCb;
    }

    function getResponse() {
        return $this->response;
    }

    function setResponse(Response $response) {
        $this->response = $response;
    }

} 
