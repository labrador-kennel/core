<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Stub;

class HandlerWithMethod {

    private $val;

    function __construct($val) {
        $this->val = $val;
    }

    function action() {
        $this->val->action = 'invoked';
    }


} 
