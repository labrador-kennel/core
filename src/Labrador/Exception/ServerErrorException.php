<?php

/**
 * Thrown if a server error occurs that should require a 500 status response.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Exception;

class ServerErrorException extends HttpException {

    function __construct($msg = '', $code = 500, $previous = null) {
        parent::__construct($msg, 500, $previous);
    }

}
