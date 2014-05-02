<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Labrador\Router\FastRouteRouter as Router;
use FastRoute\DataGenerator\GroupCountBased as GcbDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as GcbDispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit_Framework_TestCase as UnitTestCase;

class FastRouteRouterTest extends UnitTestCase {

    function testFastRouteDispatcherCallbackReturnsImproperTypeThrowsException() {
        $router = new Router(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function() { return 'not a fast-route dispatcher'; }
        );

        $expectedExc = 'Labrador\\Exception\\InvalidTypeException';
        $expectedMsg = 'A FastRoute\\Dispatcher must be returned from dispatcher callback injected in constructor';
        $this->setExpectedException($expectedExc, $expectedMsg);

        $router->match(new Request());
    }

    function testDispatcherCallbackArgumentComesFromRouteCollector() {
        $collector = $this->getMockBuilder('FastRoute\\RouteCollector')
                          ->disableOriginalConstructor()
                          ->getMock();
        $collector->expects($this->once())
                  ->method('getData')
                  ->will($this->returnValue(['routes', 'listed', 'here']));
        $actual = null;
        $dispatcher = $this->getMock('FastRoute\\Dispatcher');
        $dispatcher->expects($this->once())
                   ->method('dispatch')
                   ->will($this->returnValue([GcbDispatcher::FOUND, 'foo#bar', []]));
        $cb = function($arg) use(&$actual, $dispatcher) {
            $actual = $arg;
            return $dispatcher;
        };
        $router = new Router($collector, $cb);
        $router->match(new Request());

        $this->assertEquals(['routes', 'listed', 'here'], $actual);
    }

    function testRouterNotFoundThrowsNotFoundException() {
        $router = new Router(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
                function($data) { return new GcbDispatcher($data); }
        );

        $expectedExc = 'Labrador\\Exception\\NotFoundException';
        $expectedMsg = 'Resource Not Found';
        $this->setExpectedException($expectedExc, $expectedMsg);
        $router->match(new Request());
    }

    function testRouterMethodNotAllowedThrowsMethodNotAllowedException() {
        $router = new Router(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function($data) { return new GcbDispatcher($data); }
        );

        $request = $this->getMock('Symfony\\Component\\HttpFoundation\\Request');
        $request->expects($this->once())->method('getMethod')->will($this->returnValue('POST'));
        $request->expects($this->once())->method('getPathInfo')->will($this->returnValue('/foo'));

        $router->get('/foo', 'foo#bar');

        $expectedExc = 'Labrador\\Exception\\MethodNotAllowedException';
        $expectedMsg = 'Method Not Allowed';
        $this->setExpectedException($expectedExc, $expectedMsg);
        $router->match($request);
    }

    function testRouteMethodFoundReturnsAppropriateControllerActionString() {
        $router = new Router(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function($data) { return new GcbDispatcher($data); }
        );

        $request = $this->getMock('Symfony\\Component\\HttpFoundation\\Request');
        $request->expects($this->once())->method('getMethod')->will($this->returnValue('PUT'));
        $request->expects($this->once())->method('getPathInfo')->will($this->returnValue('/foo/bar'));

        $router->put('/foo/bar', 'foo#bar');

        $controllerAction = $router->match($request);

        $this->assertSame('foo#bar', $controllerAction);
    }

    function testRouteWithParametersSetOnRequestAttributes() {
        $router = new Router(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function($data) { return new GcbDispatcher($data); }
        );

        $router->post('/foo/{name}/{id}', 'attr#action');

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = Request::create('http://www.sprog.dev/foo/bar/qux', 'POST');
        $router->match($request);

        $actual = [];
        foreach ($request->attributes as $k => $v) {
            $actual[$k] = $v;
        }

        $expected = ['name' => 'bar', 'id' => 'qux'];
        $this->assertSame($expected, $actual);
    }

}
