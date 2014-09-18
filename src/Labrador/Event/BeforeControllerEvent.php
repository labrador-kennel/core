<?php

/**
 * Event triggered when a route was successfully found for a request.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Event;

use Labrador\Router\ResolvedRoute;
use Symfony\Component\HttpFoundation\RequestStack;

class BeforeControllerEvent extends LabradorEvent {

    private $resolvedRoute;

    /**
     * @param RequestStack $requestStack
     * @param ResolvedRoute $resolvedRoute
     */
    function __construct(RequestStack $requestStack, ResolvedRoute $resolvedRoute) {
        parent::__construct($requestStack);
        $this->resolvedRoute = $resolvedRoute;
    }

    /**
     * @return ResolvedRoute
     */
    function getResolvedRoute() {
        return $this->resolvedRoute;
    }

} 
