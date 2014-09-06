<?php

/**
 * Should determine the handling logic that should be executed for a given HTTP request.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router;

use Symfony\Component\HttpFoundation\Request;

/**
 * The $handler set in methods can be an arbitrary value; the value that you set
 * should be parseable by the HandlerResolver you use when wiring up Labrador.
 */
interface Router {

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function get($pattern, $handler);

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function post($pattern, $handler);

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function delete($pattern, $handler);

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function put($pattern, $handler);

    /**
     * @param string $method
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function custom($method, $pattern, $handler);

    /**
     * @param string $prefix
     * @param callable $cb
     * @return $this
     */
    function mount($prefix, callable $cb);

    /**
     * Should return a $handler for a matched $request or throw one of two HttpExceptions;
     * either NotFoundException or MethodNotAllowedException.
     *
     * @param Request $request
     * @return ResolvedRoute
     */
    function match(Request $request);

    /**
     * @return Route[]
     */
    function getRoutes();

    /**
     * @param callable $handler
     * @return mixed
     */
    function setNotFoundController(callable $handler);

    /**
     * @param callable $handler
     * @return mixed
     */
    function setMethodNotAllowedController(callable $handler);

} 
