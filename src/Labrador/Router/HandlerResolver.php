<?php

/**
 * Should convert a routed handler into an appropriate callable function.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router;

interface HandlerResolver {

    /**
     *
     *
     * @param mixed $handler
     * @return callable|false
     * @throws \Labrador\Exception\InvalidHandlerException
     */
    function resolve($handler);

} 
