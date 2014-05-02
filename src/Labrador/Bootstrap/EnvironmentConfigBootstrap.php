<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Bootstrap;

use Configlet\Config;
use Labrador\ConfigDirective;

class EnvironmentConfigBootstrap implements Bootstrap {

    private $config;

    function __construct(Config $config) {
        $this->config = $config;
    }

    function run() {
        $configDir = $this->config[ConfigDirective::CONFIG_DIR];
        $env = $this->config[ConfigDirective::ENVIRONMENT];

        $path = sprintf('%s/environment/%s/config.php', $configDir, $env);
        /** @var callable $cb */
        $cb = include $path;
        $cb($this->config);
    }

} 
