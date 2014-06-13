<?php

/**
 * Setup or run the appropriate actions at application startup time.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

use Labrador\Services as LabradorServices;
use Labrador\Bootstrap\IniSetBootstrap;
use Configlet\Config;
use Auryn\Injector;

return function(Injector $injector, Config $config) {

    // This service register MUST be ran OR you MUST provide an instance of
    // Labrador\Application to the $provider with appropriate dependencies defined
    (new LabradorServices())->register($injector);

    if ($config['ini'] instanceof Config) {
        (new IniSetBootstrap($config['ini']))->run();
    }

};
