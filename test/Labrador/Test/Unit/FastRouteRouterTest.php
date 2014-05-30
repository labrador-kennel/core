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

    private function getRouter() {
        return new Router(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function($data) { return new GcbDispatcher($data); }
        );
    }

    function testFastRouteDispatcherCallbackReturnsImproperTypeThrowsException() {
        $router = new Router(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function($data) { return 'not a dispatcher'; }
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
        $router = $this->getRouter();
        $expectedExc = 'Labrador\\Exception\\NotFoundException';
        $expectedMsg = 'Resource Not Found';
        $this->setExpectedException($expectedExc, $expectedMsg);
        $router->match(new Request());
    }

    function testRouterMethodNotAllowedThrowsMethodNotAllowedException() {
        $router = $this->getRouter();

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
        $router = $this->getRouter();

        $request = Request::create('http://labrador.dev/foo/bar', 'PUT');

        $router->put('/foo/bar', 'foo#bar');

        $controllerAction = $router->match($request);

        $this->assertSame('foo#bar', $controllerAction);
    }

    function testRouteWithParametersSetOnRequestAttributes() {
        $router = $this->getRouter();

        $router->post('/foo/{name}/{id}', 'attr#action');

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = Request::create('http://www.sprog.dev/foo/bar/qux', 'POST');
        $router->match($request);

        $this->assertSame('bar', $request->attributes->get('name'));
        $this->assertSame('qux', $request->attributes->get('id'));
    }

    function testLabradorMetaRequestDataSetOnRequestAttributes() {
        $router = $this->getRouter();

        $router->post('/foo', 'controller#action');

        $request = Request::create('http://labrador.dev/foo', 'POST');
        $router->match($request);

        $this->assertSame(['handler' => 'controller#action'], $request->attributes->get('_labrador'));
    }

    function testGetRoutesWithJustOne() {
        $router = $this->getRouter();
        $router->get('/foo', 'handler');

        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Labrador\\Router\\Route', $routes[0]);
        $this->assertSame('/foo', $routes[0]->getPattern());
        $this->assertSame('GET', $routes[0]->getMethod());
        $this->assertSame('handler', $routes[0]->getHandler());
    }

    function testGetRoutesWithOnePatternSupportingMultipleMethods() {
        $router = $this->getRouter();
        $router->get('/foo/bar', 'foo_bar_get');
        $router->post('/foo/bar', 'foo_bar_post');
        $router->put('/foo/bar', 'foo_bar_put');

        $expected = [
            ['GET', '/foo/bar', 'foo_bar_get'],
            ['POST', '/foo/bar', 'foo_bar_post'],
            ['PUT', '/foo/bar', 'foo_bar_put']
        ];
        $actual = [];
        $routes = $router->getRoutes();
        foreach ($routes as $route) {
            $this->assertInstanceOf('Labrador\\Router\\Route', $route);
            $actual[] = [$route->getMethod(), $route->getPattern(), $route->getHandler()];
        }

        $this->assertSame($expected, $actual);
    }

    function testGetRoutesWithStaticAndVariable() {
        $router = $this->getRouter();
        $router->get('/foo/bar/{id}', 'foo_bar_id');
        $router->get('/foo/baz/{name}', 'foo_baz_name');
        $router->post('/foo/baz', 'foo_baz_post');
        $router->put('/foo/quz', 'foo_quz_put');

        $expected = [
            ['GET', '/foo/bar/{id}', 'foo_bar_id'],
            ['GET', '/foo/baz/{name}', 'foo_baz_name'],
            ['POST', '/foo/baz', 'foo_baz_post'],
            ['PUT', '/foo/quz', 'foo_quz_put']
        ];
        $actual = [];
        $routes = $router->getRoutes();
        foreach ($routes as $route) {
            $this->assertInstanceOf('Labrador\\Router\\Route', $route);
            $actual[] = [$route->getMethod(), $route->getPattern(), $route->getHandler()];
        }

        $this->assertSame($expected, $actual);
    }

}
