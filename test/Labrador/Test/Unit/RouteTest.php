<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Labrador\Router\Route;
use Labrador\Test\Stub\ToStringHandlerObject;
use Labrador\Test\Stub\ToStringHandlerObjectWithMethod;
use PHPUnit_Framework_TestCase as UnitTestCase;

class RouteTest extends UnitTestCase {

    function testToStringHandlerIsString() {
        $route = new Route('/handler-string', 'GET', 'handler_name');
        $expected = "GET\t/handler-string\t\thandler_name";
        $this->assertEquals($expected, (string) $route);
    }

    function testToStringHandlerIsAnonymousFunction() {
        $route = new Route('/handler-anon-func', 'POST', function() {});
        $expected = "POST\t/handler-anon-func\t\tclosure{}";
        $this->assertEquals($expected, (string) $route);
    }

    function testToStringHandlerIsObject() {
        $route = new Route('/handler-object', 'GET', new ToStringHandlerObject());
        $expected = "GET\t/handler-object\t\tLabrador\\Test\\Stub\\ToStringHandlerObject";
        $this->assertEquals($expected, (string) $route);
    }

    function testToStringHandlerIsCallableArray() {
        $route = new Route('/handler-callable-array', 'GET', [new ToStringHandlerObjectWithMethod(), 'doIt']);
        $expected = "GET\t/handler-callable-array\t\tLabrador\\Test\\Stub\\ToStringHandlerObjectWithMethod::doIt";
        $this->assertEquals($expected, (string) $route);
    }

    function testToStringHandlerIsPlainArray() {
        $route = new Route('/handler-plain-array', 'GET', [1,2,3,4]);
        $expected = "GET\t/handler-plain-array\t\tArray(4)";
        $this->assertEquals($expected, (string) $route);
    }

}
