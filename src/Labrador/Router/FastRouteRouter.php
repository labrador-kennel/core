<?php

/**
 * A router that is a wrapper around the FastRoute library that adheres to
 * Labrador\Router\Router interface.
 * 
 * @license See LICENSE in source root
 *
 * @see https://github.com/nikic/FastRoute
 */

namespace Labrador\Router;

use Labrador\Exception\InvalidTypeException;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FastRouteRouter implements Router {

    private $resolver;
    private $dispatcherCb;
    private $collector;
    private $routes = [];
    private $notFoundController;
    private $methodNotFoundController;
    private $mountedPrefix;

    /**
     * Pass a FastRoute\RouteCollector and a callback that returns a FastRoute\Dispatcher.
     *
     * We ask for a callback instead of the object itself to work around needing
     * the list of routes at FastRoute dispatcher instantiation. The $dispatcherCb is
     * invoked when Router::match is called and it should expect an array of data
     * in the same format as $collector->getData().
     *
     * @param HandlerResolver $resolver
     * @param RouteCollector $collector
     * @param callable $dispatcherCb
     */
    function __construct(HandlerResolver $resolver, RouteCollector $collector, callable $dispatcherCb) {
        $this->resolver = $resolver;
        $this->collector = $collector;
        $this->dispatcherCb = $dispatcherCb;
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function get($pattern, $handler) {
        return $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function post($pattern, $handler) {
        return $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function put($pattern, $handler) {
        return $this->addRoute('PUT', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function delete($pattern, $handler) {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    /**
     * @param string $httpMethod
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    function custom($httpMethod, $pattern, $handler) {
        return $this->addRoute($httpMethod, $pattern, $handler);
    }

    /**
     * @param string $prefix
     * @param callable $cb
     * @return $this
     */
    function mount($prefix, callable $cb) {
        $this->mountedPrefix = $prefix;
        $cb($this);
        $this->mountedPrefix = null;
        return $this;
    }

    /**
     * @param $method
     * @param $pattern
     * @param $handler
     * @return $this
     */
    private function addRoute($method, $pattern, $handler) {
        // @todo implement FastRouterRouteCollector and parse required data from Route objects
        if ($this->mountedPrefix) {
            $pattern = $this->mountedPrefix . $pattern;
        }
        $this->routes[] = new Route($pattern, $method, $handler);
        $this->collector->addRoute($method, $pattern, $handler);
        return $this;
    }

    /**
     * @param Request $request
     * @return ResolvedRoute
     */
    function match(Request $request) {
        $route = $this->getDispatcher()->dispatch($request->getMethod(), $request->getPathInfo());
        $status = array_shift($route);

        if (!$route || $status === Dispatcher::NOT_FOUND) {
            return new ResolvedRoute($request, $this->getNotFoundController(), Response::HTTP_NOT_FOUND);
        }

        if ($status === Dispatcher::METHOD_NOT_ALLOWED) {
            return new ResolvedRoute($request, $this->getMethodNotAllowedController(), Response::HTTP_METHOD_NOT_ALLOWED, $route[0]);
        }

        list($handler, $params) = $route;
        $request->attributes->set('_labrador', ['handler' => $handler]);
        foreach ($params as $k => $v) {
            $request->attributes->set($k, $v);
        }

        return new ResolvedRoute($request, $this->resolver->resolve($handler), Response::HTTP_OK);
    }

    /**
     * @return Dispatcher
     * @throws InvalidTypeException
     */
    private function getDispatcher() {
        $cb = $this->dispatcherCb;
        $dispatcher = $cb($this->collector->getData());
        if (!$dispatcher instanceof Dispatcher) {
            $msg = 'A FastRoute\\Dispatcher must be returned from dispatcher callback injected in constructor';
            throw new InvalidTypeException($msg);
        }

        return $dispatcher;
    }

    function getRoutes() {
        return $this->routes;
    }

    /**
     * This function GUARANTEES that a callable will always be returned.
     *
     * @return callable
     */
    function getNotFoundController() {
        if (!$this->notFoundController) {
            return function() {
                return new Response('Not Found', Response::HTTP_NOT_FOUND);
            };
        }

        return $this->notFoundController;
    }

    /**
     * This function GUARANTEES that a callable will always be returned.
     *
     * @return callable
     */
    function getMethodNotAllowedController() {
        if (!$this->methodNotFoundController) {
            return function() {
                return new Response('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
            };
        }

        return $this->methodNotFoundController;
    }

    /**
     * Set the $controller that will be passed to the resolved route when a
     * handler could not be
     *
     *
     * @param callable $controller
     * @return $this
     */
    function setNotFoundController(callable $controller) {
        $this->notFoundController = $controller;
        return $this;
    }

    /**
     * @param callable $controller
     * @return $this
     */
    function setMethodNotAllowedController(callable $controller) {
        $this->methodNotFoundController = $controller;
        return $this;
    }

} 
