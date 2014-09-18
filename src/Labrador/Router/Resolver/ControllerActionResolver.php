<?php

/**
 * Creates a callable handler by creating a service from an Auryn\Injector as
 * defined by a specific handler format.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Router\Resolver;

use Labrador\Router\HandlerResolver;
use Labrador\Exception\InvalidHandlerException;
use Auryn\Injector;
use Auryn\InjectionException;

class ControllerActionResolver implements HandlerResolver {

    /**
     * @property Injector
     */
    private $injector;
    private $errorMsg = [
        'invalid_handler_format' => 'The handler, %s, is invalid; all handlers must have 1 hashtag delimiting the controller and action.',
        'controller_create_error' => 'An error was encountered creating the controller for %s.',
        'controller_not_callable' => 'The controller and action, %s::%s, is not callable. Please ensure that a publicly accessible method is available with this name.'
    ];

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
        if (!$this->verifyFormat($handler)) {
            return false;
        }
        // @TODO allow the explode delimiter to be configurable
        list($controllerName, $action) = explode('#', $handler);
        try {
            $controller = $this->injector->make($controllerName);
        } catch (InjectionException $exc) {
            $msg = $this->errorMsg['controller_create_error'];
            throw new InvalidHandlerException(sprintf($msg, $handler), 500, $exc);
        }

        $cb = [$controller, $action];
        if (!is_callable($cb)) {
            $msg = $this->errorMsg['controller_not_callable'];
            throw new InvalidHandlerException(sprintf($msg, $controllerName, $action), 500);
        }

        return $cb;
    }

    private function verifyFormat($handler) {
        // intentionally not checking for strict boolean false
        // we don't want to accept handlers that begin with #
        if (!strpos($handler, '#')) {
            return false;
        }

        return true;
    }

} 
