<?php

/**
 * A service register that will wire the dependency graph for the services required
 * by Labrador.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Service;

use Labrador\Application;
use Labrador\Router\FastRouteRouter;
use Labrador\Router\Router;
use Labrador\Router\HandlerResolver;
use Labrador\Router\ServiceHandlerResolver;
use Auryn\Injector;
use Configlet\MasterConfig;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;
use FastRoute\DataGenerator\GroupCountBased as GcbDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as GcbDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @codeCoverageIgnore
 */
class DefaultServicesRegister implements Register {

    /**
     * @param Injector $injector
     * @return void
     */
    function register(Injector $injector) {
        $this->registerLabradorServices($injector);
        $this->registerFastRouteServices($injector);
        $this->registerSymfonyServices($injector);
        $injector->share(MasterConfig::class);
    }

    private function registerLabradorServices(Injector $injector) {
        $injector->share(Application::class);

        $injector->share(FastRouteRouter::class);
        $injector->define(
            FastRouteRouter::class,
            [
                'collector' => RouteCollector::class,
                ':dispatcherCb' => function(array $data) use($injector) {
                    return $injector->make(GcbDispatcher::class, [':data' => $data]);
                }
            ]
        );
        $injector->alias(Router::class, FastRouteRouter::class);

        $injector->share(ServiceHandlerResolver::class);
        $injector->define(ServiceHandlerResolver::class, [ ':injector' => $injector]);
        $injector->alias(HandlerResolver::class, ServiceHandlerResolver::class);
    }

    /**
     * @param Injector $injector
     */
    private function registerFastRouteServices(Injector $injector) {
        $injector->share(RouteCollector::class);
        $injector->define(
            RouteCollector::class,
            [
                'routeParser' => StdRouteParser::class,
                'dataGenerator' => GcbDataGenerator::class
            ]
        );


    }

    private function registerSymfonyServices(Injector $injector) {
        $injector->share(Request::createFromGlobals());
        $injector->share(EventDispatcher::class);
        $injector->alias(
            EventDispatcherInterface::class,
            EventDispatcher::class
        );
    }

} 
