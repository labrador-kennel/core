<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
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

    function __construct(RouteCollector $collector, callable $dispatcherCb) {
        $this->dispatcherCb = $dispatcherCb;
        $this->collector = $collector;
    }

    function get($pattern, $controllerAction) {
        $this->collector->addRoute('GET', $pattern, $controllerAction);
    }

    function post($pattern, $controllerAction) {
        $this->collector->addRoute('POST', $pattern, $controllerAction);
    }

    function put($pattern, $controllerAction) {
        $this->collector->addRoute('PUT', $pattern, $controllerAction);
    }

    function delete($pattern, $controllerAction) {
        $this->collector->addRoute('DELETE', $pattern, $controllerAction);
    }

    function custom($httpMethod, $pattern, $controllerAction) {
        $this->collector->addRoute($httpMethod, $pattern, $controllerAction);
    }

    function match(Request $request) {
        $dispatcher = $this->getDispatcher();
        $route = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

        if (!$route || ($status = array_shift($route)) === $dispatcher::NOT_FOUND) {
            throw new NotFoundException('Resource Not Found');
        }

        if ($status === $dispatcher::METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException('Method Not Allowed');
        }

        list($controllerAction, $params) = $route;
        foreach ($params as $k => $v) {
            $request->attributes->set($k, $v);
        }

        return $controllerAction;
    }

    /**
     * @return Dispatcher
     * @throws \Labrador\Exception\InvalidTypeException
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

} 
