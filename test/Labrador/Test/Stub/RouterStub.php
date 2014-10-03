<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Stub;

use Labrador\Router\ResolvedRoute;
use Labrador\Router\Route;
use Labrador\Router\Router;
use Symfony\Component\HttpFoundation\Request;

class RouterStub implements Router {

    private $resolvedRoute;

    function __construct(ResolvedRoute $resolvedRoute = null) {
        $this->resolvedRoute = $resolvedRoute;
    }

    function addRoute($method, $pattern, $handler) {

    }

    /**
     * Should always return a ResolvedRoute that includes the controller that
     * should be invoked
     *
     * @param Request $request
     * @return ResolvedRoute
     */
    function match(Request $request) {
        return isset($this->resolvedRoute) ? $this->resolvedRoute : false;
    }

    /**
     * @return Route[]
     */
    function getRoutes() {

    }

}
