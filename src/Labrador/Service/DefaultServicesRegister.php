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

use Auryn\Injector;
use Symfony\Component\HttpFoundation\Request;

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
        $injector->share('Configlet\\MasterConfig');
    }

    private function registerLabradorServices(Injector $injector) {
        $injector->share('Labrador\\Application');

        $injector->share('Labrador\\Router\\FastRouteRouter');
        $injector->define(
            'Labrador\\Router\\FastRouteRouter',
            [
                'collector' => 'FastRoute\\RouteCollector',
                ':dispatcherCb' => function(array $data) use($injector) {
                    return $injector->make('FastRoute\\Dispatcher\\GroupCountBased', [':data' => $data]);
                }
            ]
        );
        $injector->alias('Labrador\\Router\\Router', 'Labrador\\Router\\FastRouteRouter');

        $injector->share('Labrador\\Router\\ServiceHandlerResolver');
        $injector->define('Labrador\\Router\\ServiceHandlerResolver', [ ':injector' => $injector]);
        $injector->alias('Labrador\\Router\\HandlerResolver', 'Labrador\\Router\\ServiceHandlerResolver');
    }

    /**
     * @param Injector $injector
     */
    private function registerFastRouteServices(Injector $injector) {
        $injector->share('FastRoute\\RouteCollector');
        $injector->define(
            'FastRoute\\RouteCollector',
            [
                'routeParser' => 'FastRoute\\RouteParser\\Std',
                'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased'
            ]
        );


    }

    private function registerSymfonyServices(Injector $injector) {
        $injector->share(Request::createFromGlobals());
        $injector->share('Symfony\\Component\\EventDispatcher\\EventDispatcher');
        $injector->alias(
            'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
            'Symfony\\Component\\EventDispatcher\\EventDispatcher'
        );
    }

} 
