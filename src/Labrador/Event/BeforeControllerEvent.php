<?php

/**
 * Event triggered when a route was successfully routed to a controller and before
 * that controller is invoked.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Event;

use Labrador\Router\ResolvedRoute;
use Symfony\Component\HttpFoundation\RequestStack;

class BeforeControllerEvent extends LabradorEvent {

    private $controller;

    /**
     * @param RequestStack $requestStack
     * @param callable $controller
     */
    function __construct(RequestStack $requestStack, callable $controller) {
        parent::__construct($requestStack);
        $this->controller = $controller;
    }

    function getController() {
        return $this->controller;
    }

    function setController(callable $controller) {
        $this->controller = $controller;
    }

} 
