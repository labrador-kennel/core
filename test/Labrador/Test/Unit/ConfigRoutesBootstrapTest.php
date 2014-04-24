<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Configlet\MasterConfig;
use Labrador\Bootstrap\ConfigRoutesBootstrap;
use Labrador\ConfigDirective;
use Labrador\Router\Router;
use PHPUnit_Framework_TestCase as UnitTestCase;

class ConfigRoutesBootstrapTest extends UnitTestCase {

    function testExceptionThrownIfConfigIsNotCallback() {
        $config = new MasterConfig();
        $router = $this->getMock('Labrador\\Router\\Router');
        $config[ConfigDirective::ROUTES_CALLBACK] = null;
        $bootstrap = new ConfigRoutesBootstrap($config, $router);

        $msg = 'The ' . ConfigDirective::ROUTES_CALLBACK . ' configuration must be a callable type';
        $this->setExpectedException('Labrador\\Exception\\InvalidTypeException', $msg);

        $bootstrap->run();
    }

    function testRouterPassedToCallback() {
        $config = new MasterConfig();
        $router = $this->getMock('Labrador\\Router\\Router');
        $bootstrap = new ConfigRoutesBootstrap($config, $router);

        $router->expects($this->once())->method('get')->with('/route', 'from_cb');
        $config[ConfigDirective::ROUTES_CALLBACK] = function(Router $router) {
            $router->get('/route', 'from_cb');
        };

        $bootstrap->run();
    }

} 
