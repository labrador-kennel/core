<?php

/**
 * Event triggered when a route was successfully found for a request.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Labrador\Router\ResolvedRoute;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class BeforeControllerEvent extends LabradorEvent {

    private $resolvedRoute;
    private $response;

    function __construct(RequestStack $requestStack, ResolvedRoute $resolvedRoute) {
        parent::__construct($requestStack);
        $this->resolvedRoute = $resolvedRoute;
    }

    function getResolvedRoute() {
        return $this->resolvedRoute;
    }

    function getResponse() {
        return $this->response;
    }

    function setResponse(Response $response) {
        $this->response = $response;
    }

} 
