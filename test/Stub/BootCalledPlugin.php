<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\BootablePlugin;

class BootCalledPlugin implements BootablePlugin {

    private $bootCalled = false;

    public function wasCalled() {
        return $this->bootCalled;
    }

    public function boot() : callable {
        return function() {
            $this->bootCalled = true;
        };
    }
}
