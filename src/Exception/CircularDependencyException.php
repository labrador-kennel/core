<?php

declare(strict_types = 1);

/**
 * An exception thrown if a circular dependency has been encountered that would result in an
 * infinite loop or some other state that cannot be handled properly.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Exception;

class CircularDependencyException extends Exception {
}
