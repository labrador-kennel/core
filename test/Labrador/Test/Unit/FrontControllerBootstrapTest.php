<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Configlet\Config;
use Labrador\Bootstrap\FrontControllerBootstrap;
use Labrador\ConfigDirective;
use PHPUnit_Framework_TestCase as UnitTestCase;

class FrontControllerBootstrapTest extends UnitTestCase {

    function testRunBootstrapReturnsAurynInjector() {
        $bootstrap = new FrontControllerBootstrap(function($config) {
            $config[ConfigDirective::SERVICE_REGISTERS_CALLBACK] = function() {};
        });
        $injector = $bootstrap->run();
        $this->assertInstanceOf('Auryn\\Injector', $injector);
    }

    function testConfigletConfigPassedToInjectedCallable() {
        $argVal = null;
        $cb = function($arg) use(&$argVal) {
            $arg[ConfigDirective::SERVICE_REGISTERS_CALLBACK] = function() {};
            $argVal = $arg;
        };
        $bootstrap = new FrontControllerBootstrap($cb);
        $bootstrap->run();
        $this->assertInstanceOf('Configlet\\MasterConfig', $argVal);
    }

    function testConfiguredServiceRegistersNotCallableThrowsBootupException() {
        $cb = function() {};
        $bootstrap = new FrontControllerBootstrap($cb);
        $msg = 'A %s MUST be a callable type accepting an Auryn\\Injector and a Configlet\\Config';
        $this->setExpectedException(
            'Labrador\\Exception\\BootupException',
            sprintf($msg, ConfigDirective::SERVICE_REGISTERS_CALLBACK)
        );
        $bootstrap->run();
    }

    function testConfiguredServiceRegistersCallbackPassedInjector() {
        $argVal = new \stdClass();
        $argVal->servicesArg = null;
        $configCb = function(Config $config) use($argVal) {
            $config[ConfigDirective::SERVICE_REGISTERS_CALLBACK] = function($arg) use($argVal) {
                $argVal->servicesArg = $arg;
            };
        };
        $bootstrap = new FrontControllerBootstrap($configCb);
        $bootstrap->run();
        $this->assertInstanceOf('Auryn\\Injector', $argVal->servicesArg);
    }

    function testConfiguredBootstrapCallbackRunsWithAppropriateDependencies() {
        $argVal = new \stdClass();
        $argVal->bootstrapArg0 = null;
        $argVal->bootstrapArg1 = null;
        $configCb = function(Config $config) use($argVal) {
            $config[ConfigDirective::SERVICE_REGISTERS_CALLBACK] = function() {};
            $config[ConfigDirective::BOOTSTRAPS_CALLBACK] = function($arg0, $arg1) use($argVal) {
                $argVal->bootstrapArg0 = $arg0;
                $argVal->bootstrapArg1 = $arg1;
            };
        };
        $bootstrap = new FrontControllerBootstrap($configCb);
        $bootstrap->run();
        $this->assertInstanceOf('Auryn\\Injector', $argVal->bootstrapArg0);
        $this->assertInstanceOf('Configlet\\Config', $argVal->bootstrapArg1);
    }


} 
