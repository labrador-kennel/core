<?php

/**
 * Creates a callable handler by parsing a string into an appropriate controller
 * object and method to invoke on that object.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router;

use Auryn\Injector;
use Auryn\InjectionException;
use Labrador\Exception\InvalidHandlerException;

class ProviderHandlerResolver implements HandlerResolver {

    /**
     * @property Injector
     */
    private $injector;

    /**
     * @param Injector $injector
     */
    function __construct(Injector $injector) {
        $this->injector = $injector;
    }

    /**
     * @param string $handler
     * @return callable
     * @throws InvalidHandlerException
     */
    function resolve($handler) {
        $this->verifyFormat($handler);
        list($controller, $action) = explode('#', $handler);
        try {
            $controller = $this->injector->make($controller);
        } catch (InjectionException $exc) {
            throw new InvalidHandlerException('There was an error making the requested handler', 500, $exc);
        }

        $cb = [$controller, $action];
        if (!is_callable($cb)) {
            throw new InvalidHandlerException('The controller and action specified is not appropriately callable');
        }

        return $cb;
    }

    private function verifyFormat($handler) {
        // intentionally not checking for strict boolean false
        // we don't want to accept handlers that begin with #
        if (!strpos($handler, '#')) {
            throw new InvalidHandlerException('A handler must have 1 hashtag delimiting the controller and method to invoke');
        }
    }

} 
