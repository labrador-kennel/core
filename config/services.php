<?php

/**
 * Return a callable that accepts a Auryn\Injector as the only argument and will
 * set appropriate services for ALL possible environments.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

use Labrador\Service\DefaultServicesRegister;
use Labrador\Service\DevelopmentServiceRegister;
use Labrador\ConfigDirective;
use Auryn\Injector;
use Configlet\Config;

/**
 * This function should set services against the Auryn\Injector; it should not
 * actually preform any actions on services or do anything other than set an
 * object graph and its dependencies.
 */
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
};

