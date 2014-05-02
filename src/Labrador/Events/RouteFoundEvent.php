<?php

/**
 * Event triggered when a route was successfully found for a request.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Symfony\Component\HttpFoundation\Request;

class RouteFoundEvent extends LabradorEvent {

    private $controllerCb;

    function __construct(Request $request, callable $controllerCb) {
        parent::__construct($request);
        $this->controllerCb = $controllerCb;
    }

    function getController() {
        return $this->controllerCb;
    }

    function setController(callable $controllerCb) {
        $this->controllerCb = $controllerCb;
    }

} 
