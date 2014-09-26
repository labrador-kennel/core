<?php

/**
 * Creates the Auryn Injector, primary configuration and registers Labrador's
 * default services.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador;

use Labrador\ConfigDirective;
use Labrador\Services as LabradorServices;
use Auryn\Injector;
use Auryn\Provider;
use Configlet\Config;
use Configlet\MasterConfig;

class FrontControllerBootstrap {

    /**
     * @property callable
     */
    private $configCb;

    /**
     * @param callable $appConfig
     */
    function __construct(callable $appConfig = null) {
        $this->configCb = isset($appConfig) ? $appConfig : function() {};
    }

    /**
     * @return Provider
     * @throws \Labrador\Exception\BootupException
     */
    function run() {
        $injector = new Provider();
        $config = new MasterConfig();

        $configCb = $this->configCb;
        $configCb($config);

        $injector->share($config);
        $this->runBootstrap($injector, $config);

        return $injector;
    }

    private function runBootstrap(Injector $injector, Config $config) {
        (new LabradorServices())->register($injector);
        $bootstrap = $config[ConfigDirective::BOOTSTRAP_CALLBACK];
        if (is_callable($bootstrap)) {
            $bootstrap($injector, $config);
        }
    }

} 
