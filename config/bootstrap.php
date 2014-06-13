<?php

/**
 * Setup or run the appropriate actions at application startup time.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

use Labrador\ConfigDirective;
use Labrador\Service\DefaultServicesRegister;
use Labrador\Service\DevelopmentServiceRegister;
use Labrador\Bootstrap\IniSetBootstrap;
use Configlet\Config;
use Auryn\Injector;

return function(Injector $injector, Config $config) {

    $demoTemplates = $config[ConfigDirective::ROOT_DIR] . '/src/LabradorGuide/_templates';
    $docDir = $config[ConfigDirective::ROOT_DIR] . '/doc';
    (new \LabradorGuide\Service\ControllerRegister($demoTemplates, $docDir))->register($injector);

    // This service register MUST be ran OR you MUST provide an instance of
    // Labrador\Application to the $provider with appropriate dependencies defined
    (new DefaultServicesRegister())->register($injector);

    if ($config[ConfigDirective::ENVIRONMENT] === 'development') {
        (new DevelopmentServiceRegister($config[ConfigDirective::ROOT_DIR]  . '/.git'))->register($injector);
    }

    if ($config['ini'] instanceof Config) {
        (new IniSetBootstrap($config['ini']))->run();
    }

    if ($config[\Labrador\ConfigDirective::ENVIRONMENT] === 'development') {
        $injector->make('Labrador\\Development\\HtmlToolbar')->registerEventListeners();
    }
};
