<?php

/**
 * Will set ini values for the key/value pairs in the $config passed to constructor.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Bootstrap;

use Configlet\Config;

/**
 * @codeCoverageIgnore
 */
class IniSetBootstrap implements Bootstrap {

    /**
     * @property Config
     */
    private $config;

    /**
     * @param Config $config
     */
    function __construct(Config $config) {
        $this->config = $config;
    }

    function run() {
        foreach ($this->config as $iniKey => $iniVal) {
            ini_set($iniKey, $iniVal);
        }
    }
} 
