<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\AmpEngine;

class ExtraEventEmitArgs extends AmpEngine {

    protected function eventArgs(string $eventName) : array {
        return [1, 'foo', 'bar'];
    }
}
