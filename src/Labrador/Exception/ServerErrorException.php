<?php

/**
 * Thrown if an internal server error prevents proper processing of a Request.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Exception;

class ServerErrorException extends HttpException {

    /**
     * @param string $msg
     * @param int $code
     * @param null $previous
     */
    function __construct($msg = '', $code = 500, $previous = null) {
        parent::__construct($msg, 500, $previous);
    }

}
