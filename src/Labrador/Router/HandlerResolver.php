<?php

/**
 * Should convert a routed handler into an appropriate callable function.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Router;

interface HandlerResolver {

    /**
     * If the implementation cannot turn $handler into a callable type return false.
     *
     * @param mixed $handler
     * @return callable|false
     * @throws \Labrador\Exception\InvalidHandlerException
     */
    function resolve($handler);

} 
