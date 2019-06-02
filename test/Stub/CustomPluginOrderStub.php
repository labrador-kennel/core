<?php declare(strict_types=1);


namespace Cspray\Labrador\Test\Stub;

use Auryn\Injector;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\EventAwarePlugin;
use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Cspray\Labrador\Plugin\InjectorAwarePlugin;

class CustomPluginOrderStub implements BootablePlugin, EventAwarePlugin, InjectorAwarePlugin, PluginDependentPlugin {

    private static $staticOrder = [];
    private $callOrder = [];

    public function getCallOrder() : array {
        return $this->callOrder;
    }

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot(): callable {
        return function() {
            $this->callOrder[] = 'boot';
        };
    }

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param Emitter $emitter
     * @return void
     */
    public function registerEventListeners(Emitter $emitter): void {
        $this->callOrder[] = 'events';
    }

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return iterable
     */
    public static function dependsOn(): iterable {
        self::$staticOrder[] = 'depends';
        return [];
    }

    /**
     * Register any services that the Plugin provides.
     *
     * @param Injector $injector
     * @return void
     */
    public function wireObjectGraph(Injector $injector): void {
        $this->callOrder[] = 'services';
    }

    public function customOp() {
        $this->callOrder[] = 'custom';
    }
}
