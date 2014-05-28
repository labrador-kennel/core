<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Labrador\Router\Route;
use PHPUnit_Framework_TestCase as UnitTestCase;

class RouteTest extends UnitTestCase {

    function testToStringHandlerIsString() {
        $route = new Route('/', 'GET', 'handler_name');
        $expected = "GET\t/\t\thandler_name";
        $this->assertEquals($expected, (string) $route);
    }

    function testToStringHandlerIsAnonymousFunction() {
        $route = new Route('/register', 'POST', function() {});
        $expected = "POST\t/register\t\tclosure{}";
        $this->assertEquals($expected, (string) $route);
    }

} 
