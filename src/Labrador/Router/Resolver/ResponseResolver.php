<?php

/**
 * Resolver that will take a handler that is an instanceof Response and resolve to
 * a closure that will return the Response.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Router\Resolver;

use Labrador\Router\HandlerResolver;
use Symfony\Component\HttpFoundation\Response;

class ResponseResolver implements HandlerResolver {

    /**
     * @param mixed $handler
     * @return callable|false
     */
    function resolve($handler) {
        if ($handler instanceof Response) {
            return function() use($handler) {
                return $handler;
            };
        }

        return false;
    }

} 
