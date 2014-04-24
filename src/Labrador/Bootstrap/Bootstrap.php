<?php

/**
 * An interface for instances that should perform some action at request startup.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Bootstrap;

interface Bootstrap {

    /**
     *
     *
     * @return mixed
     */
    function run();

} 
