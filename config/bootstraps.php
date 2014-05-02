<?php

/**
 * Setup or run the appropriate actions at application startup time.
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

    // if you don't run this bootstrap we assume you're have your own method
    // of adding routes to the $router; otherwise Labrador won't do anything!
    $router = $injector->make('Labrador\\Router\\FastRouteRouter');
    (new ConfigRoutesBootstrap($config, $router))->run();

};
