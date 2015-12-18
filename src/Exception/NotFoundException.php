<?php

declare(strict_types=1);

/**
 * Thrown if a requested domain entity could not be found and not finding that
 * entity upon request is an exceptional circumstance.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Exception;

class NotFoundException extends Exception {}
