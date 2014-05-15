<?php

/**
 * Testing that the appropriate actions take place when an Application handles
 * a Request.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Labrador\Application;
use Labrador\Events;
use Labrador\Exception\InvalidHandlerException;
use Labrador\Exception\MethodNotAllowedException;
use Labrador\Exception\NotFoundException;
use Labrador\Exception\ServerErrorException;
use PHPUnit_Framework_TestCase as UnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Exception as PhpException;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends UnitTestCase {

    private $eventDispatcher;
    private $router;
    private $resolver;

    function setUp() {
        $this->router = $this->getMock('Labrador\\Router\\Router');
        $this->resolver = $this->getMock('Labrador\\Router\\HandlerResolver');
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
    }

    /**
     * @return Application
     */
    private function createApplication() {
        return new Application($this->router, $this->resolver, $this->eventDispatcher);
    }

    /**
     * Provides the [Exception, HTTP status code, HTTP status message] for an
     * exception that might be thrown by a Labrador\Router\FastRouterRouter.
     *
     * @return array
     */
    function httpRouteFailureProvider() {
        return [
            [new NotFoundException(), 404, 'Not Found'],
            [new MethodNotAllowedException(), 405, 'Method Not Allowed'],
            [new PhpException('Some error message'), 500, 'Some error message']
        ];
    }

    /**
     * Ensures that exceptions thrown during routing are properly handled.g
     *
     * @param $exception
     * @param $code
     * @param $msg
     * @dataProvider httpRouteFailureProvider
     */
    function testHttpRouteFailureProperlyHandled($exception, $code, $msg) {
        $request = Request::create('http://www.labrador.dev');
        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException($exception));
        $app = $this->createApplication();
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame($msg, $response->getContent());
    }

    function testResolverErrorProperlyHandled() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->returnValue('handler#action'));

        $this->resolver->expects($this->once())
                       ->method('resolve')
                       ->with('handler#action')
                       ->will($this->throwException(new InvalidHandlerException('Fatal error creating the requested handler')));

        $app = $this->createApplication();
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Fatal error creating the requested handler', $response->getContent());
    }

    function testControllerMustReturnResponse() {
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

        $app = $this->createApplication();
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Controller actions MUST return an instance of Symfony\\Component\\HttpFoundation\\Response', $response->getContent());
    }

    function testThrowsHttpExceptionIfHandleCatchFalse() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException(new ServerErrorException('Something bad done did happen')));

        $app = $this->createApplication();
        $this->setExpectedException(
            'Labrador\\Exception\\ServerErrorException',
            'Something bad done did happen'
        );
        $app->handle($request, Application::MASTER_REQUEST, Application::DO_NOT_CATCH_EXCEPTIONS);
    }

    function testThrowsPhpExceptionIfHandleCatchFalse() {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException(new PhpException('A fatal PHP error was encountered')));

        $app = $this->createApplication();
        $this->setExpectedException(
            'Exception',
            'A fatal PHP error was encountered'
        );
        $app->handle($request, Application::MASTER_REQUEST, Application::DO_NOT_CATCH_EXCEPTIONS);
    }

    function eventProvider() {
        return [
            [0, Events::APP_HANDLE_EVENT, 'Labrador\\Events\\ApplicationHandleEvent'],
            [1, Events::ROUTE_FOUND_EVENT, 'Labrador\\Events\\RouteFoundEvent'],
            [2, Events::APP_FINISHED_EVENT, 'Labrador\\Events\\ApplicationFinishedEvent']
        ];
    }

    /**
     * @param $triggerIndex
     * @param $eventName
     * @param $eventClass
     * @dataProvider eventProvider
     */
    function testEventTriggered($triggerIndex, $eventName, $eventClass) {
        $request = Request::create('http://labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->returnValue('handler#action'));

        $cb = function() { return new Response(''); };
        $this->resolver->expects($this->once())
                       ->method('resolve')
                       ->with('handler#action')
                       ->will($this->returnValue($cb));

        $this->eventDispatcher->expects($this->at($triggerIndex))
                              ->method('dispatch')
                              ->with(
                                    $eventName,
                                    $this->callback(function($arg) use($eventClass) {
                                        return $arg instanceof $eventClass;
                                    })
                               );

        $app = $this->createApplication();
        $app->handle($request);
    }

    function exceptionThrownEventProvider() {
        return [
            [new NotFoundException()],
            [new PhpException()]
        ];
    }

    /**
     * @param $execption
     * @dataProvider exceptionThrownEventProvider
     */
    function testExceptionThrownEventTriggered($execption) {
        $request = Request::create('http://labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException($execption));

        $this->resolver->expects($this->never())->method('resolve');
        $this->eventDispatcher->expects($this->at(1))
                              ->method('dispatch')
                              ->with(
                                  Events::EXCEPTION_THROWN,
                                  $this->callback(function($arg) {
                                      return $arg instanceof Events\ExceptionThrownEvent;
                                  })
                              );

        $app = $this->createApplication();
        $app->handle($request);
    }

    function testEventResponseReturnedOnAppHandleEvent() {
        $request = Request::create('http://labrador.dev');

        $this->router->expects($this->never())->method('match');
        $this->resolver->expects($this->never())->method('resolve');

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(Events::APP_HANDLE_EVENT, function($event) {
            $event->setResponse(new Response('Called from event'));
        });

        $app = new Application($this->router, $this->resolver, $eventDispatcher);
        $response = $app->handle($request);
        $this->assertSame('Called from event', $response->getContent());
    }



}
