<?php declare(strict_types=1);


namespace Cspray\Labrador\Plugin;

/**
 * An identifying interface for objects that extend or hook into Labrador provided functionality.
 *
 * Typically you would not implement this interface directly but implement one of the interfaces that extend Plugin. It
 * is important that any custom Plugin types implement this interface or your Plugin will not be identified as such and
 * will not be loadable by the Pluggable you wish to attach it to.
 *
 * @package Cspray\Labrador\Plugin
 * @license See LICENSE in source root
 * @see \Cspray\Labrador\Plugin\BootablePlugin
 * @see \Cspray\Labrador\Plugin\EventAwarePlugin
 * @see \Cspray\Labrador\Plugin\PluginDependentPlugin
 * @see \Cspray\Labrador\Plugin\InjectorAwarePlugin
 */
interface Plugin {
}
