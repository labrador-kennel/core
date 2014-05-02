<?php

/**
 * This is the master configuration for the Labrador library; you can also use
 * this file to set any configuration values that should just always be set for
 * your application.
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

use Labrador\ConfigDirective;
use Configlet\Config;

return function(Config $config) {

    // php.ini settings that should be set regardless of environment
    // prefix any ini settings you want to change at runtime with ini.
    $config['ini.date.timezone'] = 'America/New_York';

    // Ensure that you read the documentation for each configuration value to
    // understand what is impacted when you change these values

    /**
     * ConfigDirective::ENVIRONMENT       string
     *
     * Determines the config file that is ran for a given request; e.g. if the
     * value is set to 'development' than '/config/development/config.php' is
     * ran by default. Labrador is "aware" of the environment 'production'. If a
     * value is set here but is not available in the specified configuration directory
     * then the environment will default to 'production'.
     *
     * @todo We don't actually have a test case covering this
     */
    $config[ConfigDirective::ENVIRONMENT] = 'development';

    /**
     * ConfigDirective::ROOT_DIR          string
     *
     * The root directory that Labrador and app files live under.
     */
    $config[ConfigDirective::ROOT_DIR] = dirname(__DIR__);

    /**
     * ConfigDirective::CONFIG_DIR            string
     *
     * The configuration directory that Labrador and environment configurations
     * are stored.
     */
    $config[ConfigDirective::CONFIG_DIR] = $config[ConfigDirective::ROOT_DIR] . '/config';

    /**
     * ConfigDirective::ROUTES_CALLBACK       callable
     *
     * A callback accepting a Labrador\Router\Router as the only argument. That
     * function should set the appropriate routes for the application.
     */
    $config[ConfigDirective::ROUTES_CALLBACK] = include $config[ConfigDirective::CONFIG_DIR] . '/routes.php';

    /**
     * ConfigDirective::SERVICE_REGISTERS_CALLBACK            callable
     *
     * A callback accepting a Auryn\Provider as the only argument. The function
     * should set the appropriate dependencies and services needed by the application.
     */
    $config[ConfigDirective::SERVICE_REGISTERS_CALLBACK] = include $config[ConfigDirective::CONFIG_DIR] . '/service_registers.php';

    /**
     * ConfigDirective::BOOTSTRAPS_CALLBACK               callable
     *
     * A callback accepting a Auryn\Provider as the first argument and a Configlet\Config
     * as the second argument. It should perform actions that are needed at time of
     * request startup after all configuration values have been written and all
     * services provided.
     */
    $config[ConfigDirective::BOOTSTRAPS_CALLBACK] = include $config[ConfigDirective::CONFIG_DIR] . '/bootstraps.php';

};
