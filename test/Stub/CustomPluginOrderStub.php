<?php declare(strict_types=1);


namespace Cspray\Labrador\Test\Stub;

use Amp\Promise;
use Amp\Success;
use Auryn\Injector;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\Plugin\EventAwarePlugin;
use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Cspray\Labrador\Plugin\InjectorAwarePlugin;
use stdClass;

class CustomPluginOrderStub implements BootablePlugin, EventAwarePlugin, InjectorAwarePlugin, PluginDependentPlugin {

    private static $callOrderObject;

    public static function setCallOrderObject(stdClass $stdClass) {
        self::$callOrderObject = $stdClass;
    }

    public static function clearCallOrderObject() {
        self::$callOrderObject = null;
    }

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot(): void {
        self::$callOrderObject->callOrder[] = 'boot';
    }

    /**
     * Register the event listeners your Plugin responds to.
     *
     * @param EventEmitter $emitter
     * @return void
     */
    public function registerEventListeners(EventEmitter $emitter): void {
        self::$callOrderObject->callOrder[] = 'events';
    }

    public function removeEventListeners(EventEmitter $emitter): void {
        self::$callOrderObject->callOrder[] = 'SHOULD NOT SHOW UP';
    }

    /**
     * Return an array of plugin names that this plugin depends on.
     *
     * @return array
     */
    public static function dependsOn(): array {
        self::$callOrderObject->callOrder[] = 'depends';
        return [];
    }

    /**
     * Register any services that the Plugin provides.
     *
     * @param Injector $injector
     * @return void
     */
    public function wireObjectGraph(Injector $injector): void {
        self::$callOrderObject->callOrder[] = 'services';
    }

    public function customOp() {
        self::$callOrderObject->callOrder[] = 'custom';
    }
}
