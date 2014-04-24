<?php

/**
 * Base HTTP exception where the Exception::getCode value will be used as the
 * HTTP status code in responses.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Exception;

abstract class HttpException extends Exception {

    function __construct($msg = '', $code = 500, $previous = null) {
        if ($code < 100 || $code > 599) { $code = 500; }
        parent::__construct($msg, $code, $previous);
    }

}
