<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Labrador\Application;
use Labrador\Exception\InvalidHandlerException;
use Labrador\Exception\MethodNotAllowedException;
use Labrador\Exception\NotFoundException;
use Labrador\Exception\ServerErrorException;
use PHPUnit_Framework_TestCase as UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Exception as PhpException;

class ApplicationTest extends UnitTestCase {

    private $router;

    private $resolver;

    function setUp() {
        $this->router = $this->getMock('Labrador\\Router\\Router');
        $this->resolver = $this->getMock('Labrador\\Router\\HandlerResolver');
    }

    function testRouteNotFoundReturns404Response() {
        $request = Request::create('http://www.labrador.dev');
        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException(new NotFoundException()));
        $app = new Application($this->router, $this->resolver);
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getContent());
    }

    function testRouteMethodNotAllowedReturns406Response() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException(new MethodNotAllowedException()));

        $app = new Application($this->router, $this->resolver);
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('Method Not Allowed', $response->getContent());
    }

    function testApplicationReturnsServerErrorResponseOnInvalidHandler() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->returnValue('handler#action'));

        $this->resolver->expects($this->once())
                       ->method('resolve')
                       ->with('handler#action')
                       ->will($this->throwException(new InvalidHandlerException()));

        $app = new Application($this->router, $this->resolver);
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Fatal error creating the requested handler', $response->getContent());
    }

    function testHandlerDoesNotReturnResponseServerErrorResponseReturned() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->returnValue('handler#action'));

        $this->resolver->expects($this->once())
                       ->method('resolve')
                       ->with('handler#action')
                       ->will($this->returnValue(function() {
                           return 'not a response';
                       }));

        $app = new Application($this->router, $this->resolver);
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Controller actions MUST return an instance of Symfony\\Component\\HttpFoundation\\Response', $response->getContent());
    }

    function testThrowingGenericExceptionReturnsServerErrorResponse() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException(new PhpException('Some error message')));

        $app = new Application($this->router, $this->resolver);
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Some error message', $response->getContent());
    }

    function testThrowsHttpExceptionIfHandleCatchFalse() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException(new ServerErrorException('Something bad done did happen')));

        $app = new Application($this->router, $this->resolver);
        $this->setExpectedException(
            'Labrador\\Exception\\ServerErrorException',
            'Something bad done did happen'
        );
        $app->handle($request, Application::MASTER_REQUEST, Application::DO_NOT_CATCH_EXCEPTIONS);
    }

    function testThrowsInvalidHandlerExceptionIfHandleCatchFalse() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->returnValue('handler#action'));

        $this->resolver->expects($this->once())
                       ->method('resolve')
                       ->with('handler#action')
                       ->will($this->throwException(new InvalidHandlerException('Handler could not be created')));

        $app = new Application($this->router, $this->resolver);
        $this->setExpectedException(
            'Labrador\\Exception\\InvalidHandlerException',
            'Handler could not be created'
        );
        $app->handle($request, Application::MASTER_REQUEST, Application::DO_NOT_CATCH_EXCEPTIONS);
    }

} 
