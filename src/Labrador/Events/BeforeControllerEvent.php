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

class BeforeControllerEvent extends LabradorEvent {

    private $controllerCb;

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

} 
