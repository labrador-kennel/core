<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Stub;

class BootCalledPlugin extends NameOnlyPlugin {

    private $bootCalled = false;

    public function bootCalled() {
        return $this->bootCalled;
    }

    public function boot() {
        $this->bootCalled = true;
    }

} 
