<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\AsyncEvent\StandardEvent;

class FooEventStub extends StandardEvent {

    public function __construct($target) {
        parent::__construct('foo.event', $target);
    }

}