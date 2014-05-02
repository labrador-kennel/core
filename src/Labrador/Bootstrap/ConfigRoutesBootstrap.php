<?php

/**
 * A bootstrap that will setup routes based on the callback stored in a
 * ConfigDirective::ROUTES_CALLBACK config.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Bootstrap;

use Configlet\Config;
use Labrador\ConfigDirective;
use Labrador\Exception\InvalidTypeException;
use Labrador\Router\Router;

class ConfigRoutesBootstrap implements Bootstrap {

    private $config;
    private $router;

    function __construct(Config $config, Router $router) {
        $this->config = $config;
        $this->router = $router;
    }

    function run() {
        $cb = $this->config[ConfigDirective::ROUTES_CALLBACK];
        if (!is_callable($cb)) {
            $msg = 'The %s configuration must be a callable type';
            throw new InvalidTypeException(sprintf($msg, ConfigDirective::ROUTES_CALLBACK));
        }

        call_user_func($cb, $this->router);
    }

} 
