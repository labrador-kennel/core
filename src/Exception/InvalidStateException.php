<?php declare(strict_types=1);

/**
 * An exception thrown when a Labrador\Engine is asked to do something that it cannot do because of the state the Engine
 * is in.
 *
 * @license See LICENSE in source root.
 */
namespace Cspray\Labrador\Exception;

class InvalidStateException extends Exception {
}
