<?php

namespace Cspray\Labrador\Test;

use Amp\PHPUnit\AsyncTestCase;
use Cspray\Labrador\AmpEngine;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\CoreApplicationObjectGraph;
use Auryn\Injector;
use Cspray\Labrador\Engine;
use Cspray\Labrador\Environment;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\PluginManager;
use Cspray\Labrador\Settings;
use Cspray\Labrador\SettingsLoader\ChainedSettingsStorageHandler;
use Cspray\Labrador\SettingsLoader\DefaultsWithEnvironmentOverrideSettingsLoader;
use Cspray\Labrador\SettingsLoader\JsonFileSystemSettingsStorageHandler;
use Cspray\Labrador\SettingsLoader\PhpFileSystemSettingsStorageHandler;
use Cspray\Labrador\SettingsLoader\SettingsLoader;
use Cspray\Labrador\StandardEnvironment;
use Cspray\Labrador\Test\Stub\LoggerAwareStub;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CoreApplicationObjectGraphTest extends AsyncTestCase {

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

    public function testCreateSettingsWithConfigurationPassed() {
        $settings = __DIR__ . '/resources/config/settings.json';
        $environmentDir = __DIR__ . '/resources/config/environment';
        $storageHandler = new ChainedSettingsStorageHandler(
            new PhpFileSystemSettingsStorageHandler(), new JsonFileSystemSettingsStorageHandler()
        );
        $loader = new DefaultsWithEnvironmentOverrideSettingsLoader($storageHandler, $settings, $environmentDir);
        $injector = $this->getInjector($loader);

        $settings = $injector->make(Settings::class);

        $this->assertSame(1000, $settings->get('foo.qux.foobar'));
    }

    public function testGetEnvironmentObject() {
        $injector = $this->getInjector();

        $this->assertInstanceOf(StandardEnvironment::class, $injector->make(Environment::class));
    }

    /**
     * @param SettingsLoader|null $settingsLoader
     * @return Injector
     * @throws \Cspray\Labrador\Exception\DependencyInjectionException
     */
    private function getInjector(SettingsLoader $settingsLoader = null) : Injector {
        $this->logger = new NullLogger();
        $environment = new StandardEnvironment(EnvironmentType::Development());
        return $this->getMockForAbstractClass(
            CoreApplicationObjectGraph::class, [$environment, $this->logger, $settingsLoader]
        )->wireObjectGraph();
    }
}
