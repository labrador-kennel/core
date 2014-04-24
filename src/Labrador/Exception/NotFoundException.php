<?php

/**
 * An exception thrown if the Requested resource does not have a route associated
 * to it.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Exception;

class NotFoundException extends HttpException {

    function __construct($msg = 'Not Found', $code = 404, $previous = null) {
        parent::__construct($msg, $code, $previous);
    }

}
