<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test\Stub;

use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\Plugin\BootablePlugin;

class BootCalledPlugin implements BootablePlugin {

    private $bootCalled = false;

    public function wasCalled() {
        return $this->bootCalled;
    }

    public function boot() : void {
        $this->bootCalled = true;
    }
}
