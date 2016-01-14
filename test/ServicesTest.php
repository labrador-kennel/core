<?php


namespace Cspray\Labrador\Test;

use Cspray\Labrador\{CoreEngine, Services};
use Auryn\Injector;
use PHPUnit_Framework_TestCase as UnitTestCase;

class ServicesTest extends UnitTestCase {

    public function testInjectorInstanceCreated() {
        $injector = (new Services())->createInjector();

        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testInjectorCreatesEngine() {
        $injector = (new Services())->createInjector();

        $this->assertInstanceOf(CoreEngine::class, $injector->make(CoreEngine::class));
    }

}