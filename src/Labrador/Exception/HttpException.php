<?php

/**
 * Base HTTP exception where the Exception::getCode value will be used as the
 * HTTP status code in responses.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Exception;

/**
 * @codeCoverageIgnore
 */
abstract class HttpException extends Exception {

    /**
     * @param string $msg
     * @param int $code
     * @param null $previous
     */
    function __construct($msg = '', $code = 500, $previous = null) {
        if ($code < 100 || $code > 599) { $code = 500; }
        parent::__construct($msg, $code, $previous);
    }

}
