<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router;


class CallableHandlerResolver implements HandlerResolver {

    function resolve($handler) {
        if (is_callable($handler)) {
            return $handler;
        }

        return false;
    }

}
