<?php


namespace Cspray\Labrador\Test;

use Cspray\Labrador\AmpEngine;
use Cspray\Labrador\DependencyGraph;
use Auryn\Injector;
use Cspray\Labrador\Plugin\PluginManager;
use Cspray\Labrador\Test\Stub\LoggerAwareStub;
use PHPUnit\Framework\TestCase as UnitTestCase;
use Psr\Log\LoggerInterface;

class DependencyGraphTest extends UnitTestCase {

    public function testInjectorInstanceCreated() {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $injector = (new DependencyGraph($logger))->wireObjectGraph();

        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testInjectorIsNotShared() {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $injector = (new DependencyGraph($logger))->wireObjectGraph();

        $this->assertNotSame($injector, $injector->make(Injector::class));
    }

    public function testInjectorCreatesEngine() {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $injector = (new DependencyGraph($logger))->wireObjectGraph();

        $this->assertInstanceOf(AmpEngine::class, $injector->make(AmpEngine::class));
    }

    public function testPluginManagerGetsCorrectInjector() {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $injector = (new DependencyGraph($logger))->wireObjectGraph();
        $pluginManager = $injector->make(PluginManager::class);
        $reflectedPluginManager = new \ReflectionObject($pluginManager);
        $injectorProp = $reflectedPluginManager->getProperty('injector');
        $injectorProp->setAccessible(true);
        $this->assertSame($injector, $injectorProp->getValue($pluginManager));
    }

    public function testLoggerAwareObjectsHaveLoggerSet() {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $injector = (new DependencyGraph($logger))->wireObjectGraph();

        $stub = $injector->make(LoggerAwareStub::class);

        $this->assertSame($logger, $stub->logger);
    }

    public function testLoggerIsShared() {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $injector = (new DependencyGraph($logger))->wireObjectGraph();

        $actual = $injector->make(get_class($logger));

        $this->assertSame($logger, $actual);
    }

    public function testLoggerIsAliased() {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $injector = (new DependencyGraph($logger))->wireObjectGraph();

        $actual = $injector->make(LoggerInterface::class);

        $this->assertSame($logger, $actual);
    }
}
