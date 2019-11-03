<?php

namespace Cspray\Labrador\Test;

use Amp\Log\StreamHandler;
use Amp\PHPUnit\AsyncTestCase;
use Cspray\Labrador\AmpEngine;
use Cspray\Labrador\DependencyGraph;
use Auryn\Injector;
use Cspray\Labrador\Engine;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\PluginManager;
use Cspray\Labrador\Test\Stub\LoggerAwareStub;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DependencyGraphTest extends AsyncTestCase {

    private $logger;

    public function testInjectorInstanceCreated() {
        $injector = $this->getInjector();

        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testInjectorIsNotShared() {
        $injector = $this->getInjector();

        $this->assertNotSame($injector, $injector->make(Injector::class));
    }

    public function testEngineAliasedToAmpEngine() {
        $injector = $this->getInjector();

        $this->assertInstanceOf(AmpEngine::class, $injector->make(Engine::class));
    }

    public function testEngineShared() {
        $injector = $this->getInjector();

        $this->assertSame($injector->make(Engine::class), $injector->make(Engine::class));
    }

    public function testPluggableAliasedToPluginManager() {
        $injector = $this->getInjector();

        $this->assertInstanceOf(PluginManager::class, $injector->make(Pluggable::class));
    }

    public function testPluggableShared() {
        $injector = $this->getInjector();

        $this->assertSame($injector->make(Pluggable::class), $injector->make(Pluggable::class));
    }

    public function testPluginManagerGetsCorrectInjector() {
        $injector = $this->getInjector();
        $pluginManager = $injector->make(PluginManager::class);
        $reflectedPluginManager = new \ReflectionObject($pluginManager);
        $injectorProp = $reflectedPluginManager->getProperty('injector');
        $injectorProp->setAccessible(true);
        $this->assertSame($injector, $injectorProp->getValue($pluginManager));
    }

    public function testLoggerIsShared() {
        $injector = $this->getInjector();

        $this->assertSame($injector->make(LoggerInterface::class), $injector->make(LoggerInterface::class));
    }

    public function testLoggerIsAliased() {
        $injector = $this->getInjector();

        $actual = $injector->make(LoggerInterface::class);

        $this->assertSame($this->logger, $actual);
    }

    public function testLoggerAwareObjectsHaveLoggerSet() {
        $injector = $this->getInjector();

        $stub = $injector->make(LoggerAwareStub::class);

        $this->assertSame($injector->make(LoggerInterface::class), $stub->logger);
    }

    /**
     * @return Injector
     * @throws \Cspray\Labrador\Exception\DependencyInjectionException
     */
    private function getInjector() : Injector {
        $this->logger = new NullLogger();
        $injector = (new DependencyGraph($this->logger))->wireObjectGraph();
        return $injector;
    }
}
