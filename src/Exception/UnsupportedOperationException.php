<?php

declare(strict_types=1);

/**
 * An exception thrown if a method required by an interface is not supported by a
 * specific type; the message should explain why the method is not supported.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Exception;

class UnsupportedOperationException extends Exception {}
