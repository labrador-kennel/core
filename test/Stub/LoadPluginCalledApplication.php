<?php


namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\CallbackApplication;
use function Amp\call;
use Amp\Promise;

class LoadPluginCalledApplication extends CallbackApplication {

    private $callOrder = [];

    public function loadPlugins(): Promise {
        return call(function() {
            $this->callOrder[] = "load";
        });
    }

    public function start(): Promise {
        return call(function() {
            $this->callOrder[] = "execute";
        });
    }

    public function callOrder() : array {
        return $this->callOrder;
    }
}
