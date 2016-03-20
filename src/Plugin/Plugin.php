<?php

declare(strict_types=1);

/**
 * An identifying interface for objects that extend or hook into Labrador provided functionality; typically you would
 * not implement this interface directly but implement one of the interfaces that extend Plugin.
 *
 * @license See LICENSE in source root
 * 
 * @see \Cspray\Labrador\Plugin\BootablePlugin
 * @see \Cspray\Labrador\Plugin\EventAwarePlugin
 * @see \Cspray\Labrador\Plugin\PluginDependentPlugin
 * @see \Cspray\Labrador\Plugin\ServiceAwarePlugin
 */

namespace Cspray\Labrador\Plugin;

interface Plugin {}
