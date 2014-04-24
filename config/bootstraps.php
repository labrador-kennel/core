<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

use \Labrador\Bootstrap\ConfigRoutesBootstrap;
use Labrador\Bootstrap\EnvironmentConfigBootstrap;
use Labrador\Bootstrap\IniSetBootstrap;
use Configlet\Config;
use Auryn\Injector;

return function(Injector $injector, Config $config) {

    (new EnvironmentConfigBootstrap($config))->run();
    if ($config['ini'] instanceof Config) { (new IniSetBootstrap($config['ini']))->run(); }
    $router = $injector->make('Labrador\\Router\\FastRouteRouter');
    (new ConfigRoutesBootstrap($config, $router))->run();

};
