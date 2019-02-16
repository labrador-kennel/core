<?php


namespace Cspray\Labrador\Test;

use Cspray\Labrador\AmpEngine;
use Cspray\Labrador\DependencyGraph;
use Auryn\Injector;
use Cspray\Labrador\PluginManager;
use PHPUnit\Framework\TestCase as UnitTestCase;

class DependencyGraphTest extends UnitTestCase {

    public function testInjectorInstanceCreated() {
        $injector = (new DependencyGraph())->wireObjectGraph();

        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testInjectorIsNotShared() {
        $injector = (new DependencyGraph())->wireObjectGraph();

        $this->assertNotSame($injector, $injector->make(Injector::class));
    }

    public function testInjectorCreatesEngine() {
        $injector = (new DependencyGraph())->wireObjectGraph();

        $this->assertInstanceOf(AmpEngine::class, $injector->make(AmpEngine::class));
    }

    public function testPluginManagerGetsCorrectInjector() {
        $injector = (new DependencyGraph())->wireObjectGraph();
        $pluginManager = $injector->make(PluginManager::class);
        $reflectedPluginManager = new \ReflectionObject($pluginManager);
        $injectorProp = $reflectedPluginManager->getProperty('injector');
        $injectorProp->setAccessible(true);
        $this->assertSame($injector, $injectorProp->getValue($pluginManager));
    }
}
