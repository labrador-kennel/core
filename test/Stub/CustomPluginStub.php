<?php declare(strict_types=1);


namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\Plugin\Plugin;

class CustomPluginStub implements Plugin {

    private $timesCalled = 0;

    public function myCustomPlugin() {
        $this->timesCalled++;
    }

    public function getTimesCalled() {
        return $this->timesCalled;
    }
}
