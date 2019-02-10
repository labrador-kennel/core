<?php

declare(strict_types = 1);

/**
 * A Plugin depended on by a PluginDependentPlugin has not been registered with a
 * given Pluggable.
 *
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Exception;

class PluginDependencyNotProvidedException extends NotFoundException {
}
