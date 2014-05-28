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
use Symfony\Component\HttpFoundation\Response;

class FastRouteRouter implements Router {

    private $dispatcherCb;
    private $collector;

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
        $this->dispatcherCb = $dispatcherCb;
        $this->collector = $collector;
    }

    /**
     * @param string $pattern
     * @param mixed $controllerAction
     * @return $this|void
     */
    function get($pattern, $controllerAction) {
        $this->collector->addRoute('GET', $pattern, $controllerAction);
    }

    /**
     * @param string $pattern
     * @param mixed $controllerAction
     * @return $this|void
     */
    function post($pattern, $controllerAction) {
        $this->collector->addRoute('POST', $pattern, $controllerAction);
    }

    /**
     * @param string $pattern
     * @param mixed $controllerAction
     * @return $this|void
     */
    function put($pattern, $controllerAction) {
        $this->collector->addRoute('PUT', $pattern, $controllerAction);
    }

    /**
     * @param string $pattern
     * @param mixed $controllerAction
     * @return $this|void
     */
    function delete($pattern, $controllerAction) {
        $this->collector->addRoute('DELETE', $pattern, $controllerAction);
    }

    /**
     * @param string $httpMethod
     * @param string $pattern
     * @param mixed $controllerAction
     * @return $this|void
     */
    function custom($httpMethod, $pattern, $controllerAction) {
        $this->collector->addRoute($httpMethod, $pattern, $controllerAction);
    }

    /**
     * @param Request $request
     * @return string
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    function match(Request $request) {
        $dispatcher = $this->getDispatcher();
        $route = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
        $status = array_shift($route);

        if (!$route || $status === $dispatcher::NOT_FOUND) {
            throw new NotFoundException('Resource Not Found');
        }

        if ($status === $dispatcher::METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException('Method Not Allowed');
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

    }

} 
