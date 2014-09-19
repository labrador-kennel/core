<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit\Router;

use Labrador\Router\HandlerResolver;
use Labrador\Router\ResolvedRoute;
use Labrador\Router\FastRouteRouter as Router;
use FastRoute\DataGenerator\GroupCountBased as GcbDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as GcbDispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit_Framework_TestCase as UnitTestCase;

class FastRouteRouterTest extends UnitTestCase {

    private $mockResolver;

    private function getRouter() {
        $this->mockResolver = $this->getMock(HandlerResolver::class);
        return new Router(
            $this->mockResolver,
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function($data) { return new GcbDispatcher($data); }
        );
    }

    function testFastRouteDispatcherCallbackReturnsImproperTypeThrowsException() {
        $mockResolver = $this->getMock(HandlerResolver::class);
        $router = new Router(
            $mockResolver,
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function() { return 'not a dispatcher'; }
        );

        $expectedExc = 'Labrador\\Exception\\InvalidTypeException';
        $expectedMsg = 'A FastRoute\\Dispatcher must be returned from dispatcher callback injected in constructor';
        $this->setExpectedException($expectedExc, $expectedMsg);

        $router->match(new Request());
    }

    function testRouterNotFoundReturnsCorrectResolvedRoute() {
        $router = $this->getRouter();
        $resolved = $router->match(new Request());
        $this->assertInstanceOf(ResolvedRoute::class, $resolved);
        $this->assertTrue($resolved->isNotFound());
        $handler = $resolved->getController();
        /** @var Response $response */
        $response = $handler(new Request());
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getContent());
    }

    function testRouterMethodNotAllowedReturnsCorrectResolvedRoute() {
        $router = $this->getRouter();
        $request = Request::create('http://labrador.dev/foo', 'POST');
        $router->get('/foo', 'foo#bar');
        $router->put('/foo', 'foo#baz');

        $resolved = $router->match($request);
        $this->assertInstanceOf(ResolvedRoute::class, $resolved);
        $this->assertTrue($resolved->isMethodNotAllowed());
        $this->assertSame(['GET', 'PUT'], $resolved->getAvailableMethods());
        $handler = $resolved->getController();
        /** @var Response $response */
        $response = $handler($request);
        $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertSame('Method Not Allowed', $response->getContent());
    }

    function testRouterIsOkReturnsCorrectResolvedRoute() {
        $router = $this->getRouter();
        $request = Request::create('http://labrador.dev/foo', 'GET');
        $router->get('/foo', 'handler');
        $this->mockResolver->expects($this->once())->method('resolve')->with('handler')->will($this->returnValue(function() { return 'OK'; }));

        $resolved = $router->match($request);
        $this->assertInstanceOf(ResolvedRoute::class, $resolved);
        $this->assertTrue($resolved->isOk());
        $handler = $resolved->getController();
        $this->assertSame('OK', $handler());
    }

    function testRouteWithParametersSetOnRequestAttributes() {
        $router = $this->getRouter();

        $router->post('/foo/{name}/{id}', 'attr#action');
        $this->mockResolver->expects($this->once())->method('resolve')->with('attr#action')->will($this->returnValue(function() { return 'OK'; }));

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = Request::create('http://www.sprog.dev/foo/bar/qux', 'POST');
        $router->match($request);

        $this->assertSame('bar', $request->attributes->get('name'));
        $this->assertSame('qux', $request->attributes->get('id'));
    }

    function testLabradorMetaRequestDataSetOnRequestAttributes() {
        $router = $this->getRouter();

        $router->post('/foo', 'controller#action');
        $this->mockResolver->expects($this->once())->method('resolve')->with('controller#action')->will($this->returnValue(function() { return 'OK'; }));

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

    function testMountingRouterAddsPrefix() {
        $router = $this->getRouter();
        $router->mount('/prefix', function(Router $router) {
            $router->get('/foo', 'something');
        });
        $router->get('/noprefix', 'something else');

        $expected = [
            ['GET', '/prefix/foo', 'something'],
            ['GET', '/noprefix', 'something else']
        ];
        $actual = [];
        $routes = $router->getRoutes();
        foreach ($routes as $route) {
            $actual[] = [$route->getMethod(), $route->getPattern(), $route->getHandler()];
        }

        $this->assertSame($expected, $actual);
    }

}
