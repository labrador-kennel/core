<?php

/**
 * An exception thrown if the Request matches a route but is not an allowed method.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Exception;

/**
 * @codeCoverageIgnore
 */
class MethodNotAllowedException extends HttpException {

    /**
     * @param string $msg
     * @param int $code
     * @param null $previous
     */
    function __construct($msg = 'Method Not Allowed', $code = 405, $previous = null) {
        parent::__construct($msg, 405, $previous);
    }

}
