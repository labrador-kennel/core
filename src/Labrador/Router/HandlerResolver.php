<?php

/**
 * Should convert a routed handler into an appropriate callable function that
 * accepts a Request object as the only argument.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router;

interface HandlerResolver {

    /**
     * @param mixed $handler
     * @return callable
     * @throws \Labrador\Exception\Exception
     */
    function resolve($handler);

} 
