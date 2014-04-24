<?php

/**
 * An exception thrown if the Request matches a route but is not an allowed method.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Exception;

class MethodNotAllowedException extends HttpException {

    function __construct($msg = 'Method Not Allowed', $code = 405, $previous = null) {
        parent::__construct($msg, 405, $previous);
    }

}
