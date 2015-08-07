<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\Plugin;

class BootCalledPlugin implements Plugin {

    private $bootCalled = false;

    public function wasCalled() {
        return $this->bootCalled;
    }

    public function boot() {
        $this->bootCalled = true;
    }

} 