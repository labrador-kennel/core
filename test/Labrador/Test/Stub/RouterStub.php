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

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function get($pattern, $handler) {

    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function post($pattern, $handler) {

    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function delete($pattern, $handler) {

    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function put($pattern, $handler) {

    }

    /**
     * @param string $method
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function custom($method, $pattern, $handler) {

    }

    /**
     * @param string $prefix
     * @param callable $cb
     * @return $this
     */
    function mount($prefix, callable $cb) {

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

    /**
     * @param callable $handler
     * @return mixed
     */
    function setNotFoundController(callable $handler) {

    }

    /**
     * @param callable $handler
     * @return mixed
     */
    function setMethodNotAllowedController(callable $handler) {

    }

}
