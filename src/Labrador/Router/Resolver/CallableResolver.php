<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router\Resolver;

use Labrador\Router\HandlerResolver;

class CallableResolver implements HandlerResolver {

    function resolve($handler) {
        if (is_callable($handler)) {
            return $handler;
        }

        return false;
    }

}
