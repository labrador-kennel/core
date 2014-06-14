<?php

/**
 * Creates the Auryn Injector, primary configuration and boots up the application.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Bootstrap;

use Labrador\ConfigDirective;
use Labrador\Services as LabradorServices;
use Auryn\Injector;
use Auryn\Provider;
use Configlet\Config;
use Configlet\MasterConfig;

class FrontControllerBootstrap implements Bootstrap {

    /**
     * @property callable
     */
    private $configCb;

    /**
     * @param callable $appConfig
     */
    function __construct(callable $appConfig) {
        $this->configCb = $appConfig;
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
