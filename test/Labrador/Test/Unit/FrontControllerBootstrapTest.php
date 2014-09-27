<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Auryn\Provider;
use Labrador\Application;
use Labrador\ConfigDirective;
use Labrador\FrontControllerBootstrap;
use PHPUnit_Framework_TestCase as UnitTestCase;

class FrontControllerBootstrapTest extends UnitTestCase {

    function testBootstrapReturnsProvider() {
        $provider = (new FrontControllerBootstrap())->run();
        $this->assertInstanceOf(Provider::class, $provider);
    }

    function testProviderCreatesApplication() {
        $provider = (new FrontControllerBootstrap())->run();
        $this->assertInstanceOf(Application::class, $provider->make(Application::class));
    }

    function testUserProvidedBootstrapInvoked() {
        $invoked = false;
        $appConfig = function($config) use(&$invoked) {
            $config[ConfigDirective::BOOTSTRAP_CALLBACK] = function() use(&$invoked) { $invoked = true; };
        };
        (new FrontControllerBootstrap($appConfig))->run();
        $this->assertTrue($invoked);
    }

} 
