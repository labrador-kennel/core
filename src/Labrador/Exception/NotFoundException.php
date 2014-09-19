<?php

/**
 * An exception thrown if the Requested resource could not be found.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Exception;

class NotFoundException extends HttpException {

    /**
     * @param string $msg
     * @param int $code
     * @param null $previous
     */
    function __construct($msg = 'Not Found', $code = 404, $previous = null) {
        parent::__construct($msg, $code, $previous);
    }

}
