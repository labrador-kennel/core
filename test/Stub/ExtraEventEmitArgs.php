<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\CoreEngine;

class ExtraEventEmitArgs extends CoreEngine {

    protected function eventArgs(string $eventName) : array {
        return [1, 'foo', 'bar'];
    }
}
