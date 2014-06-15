<?php

/**
 * A router that is a wrapper around nikic's FastRoute implementation.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 *
 * @see https://github.com/nikic/FastRoute
 */

namespace Labrador\Router;

use Labrador\Exception\InvalidTypeException;
use Labrador\Exception\NotFoundException;
use Labrador\Exception\MethodNotAllowedException;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;

class FastRouteRouter implements Router {

    private $dispatcherCb;
    private $collector;
    private $routes = [];

    /**
     * Pass a FastRoute\RouteCollector and a callback that returns a FastRoute\Dispatcher.
     *
     * We ask for a callback instead of the object itself to work around needing
     * the list of routes at dispatcher instantiation. The $dispatcherCb is
     * invoked when Router::match is called and it should expect an array of data
     * in the same format as $collector->getData().
     *
     * @param RouteCollector $collector
     * @param callable $dispatcherCb
     */
    function __construct(RouteCollector $collector, callable $dispatcherCb) {
        $this->collector = $collector;
        $this->dispatcherCb = $dispatcherCb;
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this|void
     */
    function get($pattern, $handler) {
        return $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this|void
     */
    function post($pattern, $handler) {
        return $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this|void
     */
    function put($pattern, $handler) {
        return $this->addRoute('PUT', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this|void
     */
    function delete($pattern, $handler) {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    /**
     * @param string $httpMethod
     * @param string $pattern
     * @param mixed $handler
     * @return $this|void
     */
    function custom($httpMethod, $pattern, $handler) {
        return $this->addRoute($httpMethod, $pattern, $handler);
    }

    private function addRoute($method, $pattern, $handler) {
        // @todo implement FastRouterRouteCollector and parse required data from Route objects
        $this->routes[] = new Route($pattern, $method, $handler);
        $this->collector->addRoute($method, $pattern, $handler);
        return $this;
    }

    /**
     * @param Request $request
     * @return string
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    function match(Request $request) {
        $dispatcher = $this->getDispatcher();
        $method = $request->getMethod();
        $path = $request->getPathInfo();
        $route = $dispatcher->dispatch($method, $path);
        $status = array_shift($route);

        if (!$route || $status === $dispatcher::NOT_FOUND) {
            $msg = 'The route %s %s could not be found.';
            throw new NotFoundException(sprintf($msg, $method, $path));
        }

        if ($status === $dispatcher::METHOD_NOT_ALLOWED) {
            $msg = 'The method %s is not allowed for route matching %s. Available methods include [%s]';
            throw new MethodNotAllowedException(sprintf($msg, $method, $path, implode(', ', $route[0])));
        }

        list($handler, $params) = $route;
        $request->attributes->set('_labrador', ['handler' => $handler]);
        foreach ($params as $k => $v) {
            $request->attributes->set($k, $v);
        }

        return $handler;
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

} 
