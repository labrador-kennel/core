<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Auryn\Injector;
use Auryn\InjectorException;
use Cspray\Labrador\AsyncEvent\AmpEventEmitter;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\Exception\DependencyInjectionException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\PluginManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * @package Cspray\Labrador
 */
abstract class CoreApplicationObjectGraph implements ApplicationObjectGraph {

    private $logger;

    private $environment;

    private $settingsLoader;

    public function __construct(Environment $environment, LoggerInterface $logger, SettingsLoader $settingsLoader = null) {
        $this->environment = $environment;
        $this->logger = $logger;
        $this->settingsLoader = $settingsLoader;
    }

    public function wireObjectGraph() : Injector {
        try {
            $injector = new Injector();

            if (isset($this->settingsLoader)) {
                $settings = $this->settingsLoader->loadSettings($this->environment);
                $injector->share($settings);
                $injector->alias(Settings::class, get_class($settings));
            }

            $injector->share($this->environment);
            $injector->alias(Environment::class, get_class($this->environment));

            $injector->share(EventEmitter::class);
            $injector->alias(EventEmitter::class, AmpEventEmitter::class);

            $injector->share(Pluggable::class);
            $injector->alias(Pluggable::class, PluginManager::class);
            $injector->define(PluginManager::class, [
                ':injector' => $injector
            ]);

            $injector->share(Engine::class);
            $injector->alias(Engine::class, AmpEngine::class);

            $logger = $this->logger;
            $injector->share($logger);
            $injector->alias(LoggerInterface::class, get_class($logger));
            $injector->prepare(LoggerAwareInterface::class, function(LoggerAwareInterface $aware) use($logger) {
                $aware->setLogger($logger);
            });

            return $injector;
        } catch (InjectorException $injectorException) {
            /** @var DependencyInjectionException $exception */
            $exception = Exceptions::createException(Exceptions::DEPENDENCY_GRAPH_INJECTION_ERR, $injectorException);
            throw $exception;
        }
    }
}