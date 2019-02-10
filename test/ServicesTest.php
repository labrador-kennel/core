<?php


namespace Cspray\Labrador\Test;

use Cspray\Labrador\CoreEngine;
use Cspray\Labrador\Services;
use Auryn\Injector;
use PHPUnit\Framework\TestCase as UnitTestCase;

class ServicesTest extends UnitTestCase {

    public function testInjectorInstanceCreated() {
        $injector = (new Services())->wireObjectGraph();

        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testInjectorCreatesEngine() {
        $injector = (new Services())->wireObjectGraph();

        $this->assertInstanceOf(CoreEngine::class, $injector->make(CoreEngine::class));
    }
}
