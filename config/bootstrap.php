<?php

/**
 * Setup or run the appropriate actions at application startup time.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

use Labrador\Bootstrap\IniSetBootstrap;
use LabradorGuide\Services as GuideServices;
use Configlet\Config;
use Auryn\Injector;

return function(Injector $injector, Config $config) {

    (new GuideServices())->register($injector);

    if ($config['ini'] instanceof Config) {
        (new IniSetBootstrap($config['ini']))->run();
    }

};
