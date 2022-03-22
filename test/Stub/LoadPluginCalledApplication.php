<?php


namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\Pluggable;

class LoadPluginCalledApplication extends TestApplication {

    private $callOrder = [];

    public function __construct(Pluggable $pluggable) {
        parent::__construct(
            $pluggable,
            function() {
                $this->callOrder[] = 'doStart';
            }
        );
    }

    public function loadPlugins(): void {
        $this->callOrder[] = "load";
    }

    public function callOrder() : array {
        return $this->callOrder;
    }
}
