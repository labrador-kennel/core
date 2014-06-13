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
     * @param callable $appConfig
     */
    function __construct(callable $appConfig) {
        $this->configCb = $appConfig;
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

        $this->runBootstrap($injector, $config);

        return $injector;
    }

    private function runBootstrap(Injector $injector, Config $config) {
        $bootstraps = $config[ConfigDirective::BOOTSTRAP_CALLBACK];
        if (is_callable($bootstraps)) {
            $bootstraps($injector, $config);
        }
    }

} 
