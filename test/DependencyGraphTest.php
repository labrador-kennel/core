<?php

namespace Cspray\Labrador\Test;

use Amp\Log\StreamHandler;
use Amp\PHPUnit\AsyncTestCase;
use Cspray\Labrador\AmpEngine;
use Cspray\Labrador\Configuration;
use Cspray\Labrador\DependencyGraph;
use Auryn\Injector;
use Cspray\Labrador\Plugin\PluginManager;
use Cspray\Labrador\Test\Stub\LoggerAwareStub;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class DependencyGraphTest extends AsyncTestCase {

    private function getConfiguration() : Configuration {
        return new class implements Configuration {

            /**
             * Return the name of the log that Monolog will use to identify log messages from this Application.
             *
             * @return string
             */
            public function getLogName() : string {
                return 'dependency-graph-test';
            }

            /**
             * Return a path that can be used as a resource stream to write log messages to.
             *
             * @return string
             */
            public function getLogPath() : string {
                return 'php://memory';
            }

            /**
             * Return a path in which the file returns a callable that accepts a single Configuration instance and
             * returns an Injector.
             *
             * @return string
             */
            public function getInjectorProviderPath() : string {
                throw new \RuntimeException('Did not expect this to be called');
            }

            /**
             * Return a Set of fully qualified class names for the Plugins that should be added to your Application.
             *
             * @return Set<string>
             */
            public function getPlugins() : array {
                throw new \RuntimeException('Did not expect this to be called');
            }
        };
    }

    public function testInjectorInstanceCreated() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();

        $this->assertInstanceOf(Injector::class, $injector);
    }

    public function testInjectorIsNotShared() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();

        $this->assertNotSame($injector, $injector->make(Injector::class));
    }

    public function testInjectorCreatesEngine() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();

        $this->assertInstanceOf(AmpEngine::class, $injector->make(AmpEngine::class));
    }

    public function testPluginManagerGetsCorrectInjector() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();
        $pluginManager = $injector->make(PluginManager::class);
        $reflectedPluginManager = new \ReflectionObject($pluginManager);
        $injectorProp = $reflectedPluginManager->getProperty('injector');
        $injectorProp->setAccessible(true);
        $this->assertSame($injector, $injectorProp->getValue($pluginManager));
    }

    public function testLoggerIsShared() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();

        $this->assertSame($injector->make(LoggerInterface::class), $injector->make(LoggerInterface::class));
    }

    public function testLoggerIsAliased() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();

        $actual = $injector->make(LoggerInterface::class);
        $expected = Logger::class;

        $this->assertInstanceOf($expected, $actual);
    }

    public function testLoggerAwareObjectsHaveLoggerSet() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();

        $stub = $injector->make(LoggerAwareStub::class);

        $this->assertSame($injector->make(LoggerInterface::class), $stub->logger);
    }

    public function testLoggerHasConfiguredName() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();

        /** @var Logger $logger */
        $logger = $injector->make(LoggerInterface::class);

        $this->assertSame('dependency-graph-test', $logger->getName());
    }

    public function testLoggerHasStreamingHandler() {
        $injector = (new DependencyGraph($this->getConfiguration()))->wireObjectGraph();

        /** @var Logger $logger */
        $logger = $injector->make(LoggerInterface::class);

        $this->assertCount(1, $logger->getHandlers());
        $this->assertInstanceOf(StreamHandler::class, $logger->getHandlers()[0]);
    }
}
