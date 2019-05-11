<?php declare(strict_types=1);

/**
 * A special type of Plugin for the Labrador framework that acts as the primary execution point for your application.
 *
 * @license See LICENSE in source root.
 */
namespace Cspray\Labrador;

use Amp\Promise;
use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\EventAwarePlugin;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Cspray\Labrador\Plugin\InjectorAwarePlugin;
use Throwable;

interface Application extends BootablePlugin, EventAwarePlugin, PluginDependentPlugin, InjectorAwarePlugin {

    /**
     * Perform whatever logic or operations your application requires; return a Promise that resolves when you app is
     * finished running.
     *
     * This method should avoid throwing an exception and instead fail the Promise with the Exception that caused the
     * application to crash.
     *
     * @return Promise
     */
    public function execute() : Promise;

    /**
     * Handle an exception being thrown in your application; if you can gracefully handle the exception the app will
     * continue to run otherwise rethrow the exception to cause the application to shutdown.
     *
     * @param Throwable $throwable
     * @return void
     */
    public function exceptionHandler(Throwable $throwable) : void;
}
