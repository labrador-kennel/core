<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use League\Event\Event;

class FooEventStub extends Event {

    public function __construct() {
        parent::__construct('foo.event');
    }

}