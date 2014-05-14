<?php

/**
 * A bootstrap that will register all services and run all other bootstraps.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Bootstrap;

use Labrador\ConfigDirective;
use Labrador\Exception\BootupException;
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
     * @param callable $masterConfig
     */
    function __construct(callable $masterConfig) {
        $this->configCb = $masterConfig;
    }

    /**
     * @return Provider|mixed
     * @throws \Labrador\Exception\BootupException
     */
    function run() {
        $injector = new Provider();
        $config = new MasterConfig();

        $configCb = $this->configCb;
        $configCb($config);

        $this->runServices($injector, $config);
        $this->runBootstrap($injector, $config);

        return $injector;
    }

    private function runServices(Injector $injector, Config $config) {
        $services = $config[ConfigDirective::SERVICE_REGISTERS_CALLBACK];
        if (!is_callable($services)) {
            $msg = 'A %s MUST be a callable type accepting an Auryn\\Injector and a Configlet\\Config';
            throw new BootupException(sprintf($msg, ConfigDirective::SERVICE_REGISTERS_CALLBACK));
        }
        $services($injector, $config);
    }

    private function runBootstrap(Injector $injector, Config $config) {
        $bootstraps = $config[ConfigDirective::BOOTSTRAPS_CALLBACK];
        if (is_callable($bootstraps)) {
            $bootstraps($injector, $config);
        }
    }

} 
