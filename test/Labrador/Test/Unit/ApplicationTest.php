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
use Labrador\Router\ResolvedRoute;
use PHPUnit_Framework_TestCase as UnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Exception as PhpException;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends UnitTestCase {

    private $eventDispatcher;
    private $router;
    private $requestStack;

    function setUp() {
        $this->router = $this->getMock('Labrador\\Router\\Router');
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
        $this->requestStack = $this->getMock('Symfony\\Component\\HttpFoundation\\RequestStack');
    }

    /**
     * @return Application
     */
    private function createApplication() {
        return new Application($this->router, $this->eventDispatcher, $this->requestStack);
    }

    function testControllerMustReturnResponse() {
        $request = Request::create('http://www.labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { return 'handler#action'; }, Response::HTTP_OK);
        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->returnValue($resolvedRoute));

        $app = $this->createApplication();
        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(500, $response->getStatusCode());
        $msg = 'Controllers MUST return an instance of Symfony\\Component\\HttpFoundation\\Response.';
        $msg .= ' The handler returned type (string).';
        $this->assertSame($msg, $response->getContent());
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
        $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
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
        $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
    }

    function eventProvider() {
        return [
            [0, Events::APP_HANDLE, 'Labrador\\Events\\ApplicationHandleEvent'],
            [1, Events::ROUTE_FOUND, 'Labrador\\Events\\RouteFoundEvent'],
            [2, Events::APP_FINISHED, 'Labrador\\Events\\ApplicationFinishedEvent']
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
        $resolved = new ResolvedRoute($request, function() { return new Response(''); }, Response::HTTP_OK);
        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->returnValue($resolved));

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

    function exceptionThrownProvider() {
        return [
            [new PhpException('this is the message why something failed')],
            [new NotFoundException('this is the resource not found')]
        ];
    }

    /**
     * @param $exception
     * @dataProvider exceptionThrownProvider
     */
    function testExceptionThrownEventTriggered($exception) {
        $request = Request::create('http://labrador.dev');

        $this->router->expects($this->once())
                     ->method('match')
                     ->with($request)
                     ->will($this->throwException($exception));

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

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(Events::APP_HANDLE, function($event) {
            $event->setResponse(new Response('Called from event'));
        });

        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $response = $app->handle($request);
        $this->assertSame('Called from event', $response->getContent());
    }

    function testAppFinishedEventTriggeredWhenResponseReturnedOnAppHandleEvent() {
        $request = Request::create('http://labrador.dev');

        $this->router->expects($this->never())->method('match');

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(Events::APP_HANDLE, function($event) {
            $event->setResponse(new Response('called from event'));
        });

        $finishCalled = false;
        $eventDispatcher->addListener(Events::APP_FINISHED, function($event) use(&$finishCalled) {
            $finishCalled = true;
        });

        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $response = $app->handle($request);
        $this->assertSame('called from event', $response->getContent());
        $this->assertTrue($finishCalled);
    }

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testResponseSetInPhpExceptionThrownEventIsReturned($exception) {
        $request = Request::create('http://labrador.dev');
        $this->router->expects($this->once())
                     ->method('match')
                     ->will($this->throwException($exception));

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(Events::EXCEPTION_THROWN, function($event) {
            $event->setResponse(new Response('Called from exception thrown listener'));
        });

        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $response = $app->handle($request);
        $this->assertSame('Called from exception thrown listener', $response->getContent());
    }

    function testResponseSetInAppFinishedEventIsReturned() {
        $request = Request::create('http://labrador.dev');
        $resolved = new ResolvedRoute($request, function() { return new Response(''); }, Response::HTTP_OK);

        $this->router->expects($this->once())->method('match')->will($this->returnValue($resolved));

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(Events::APP_FINISHED, function($event) {
            $event->setResponse(new Response('called from the app_finished listener'));
        });

        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $response = $app->handle($request);
        $this->assertSame('called from the app_finished listener', $response->getContent());
    }

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testAppFinishedEventTriggeredWhenExceptionCaught($exception) {
        $request = Request::create('http://labrador.dev');
        $this->router->expects($this->once())
                     ->method('match')
                     ->will($this->throwException($exception));

        // The 0 call is AppHandleEvent
        $this->eventDispatcher->expects($this->at(1))
                              ->method('dispatch')
                              ->with(
                                    Events::EXCEPTION_THROWN,
                                    $this->callback(function($arg) {
                                        return $arg instanceof Events\ExceptionThrownEvent;
                                    })
                                );

        $this->eventDispatcher->expects($this->at(2))
                              ->method('dispatch')
                              ->with(
                                  Events::APP_FINISHED,
                                  $this->callback(function($arg) {
                                      return $arg instanceof Events\ApplicationFinishedEvent;
                                  })
                                );

        $app = $this->createApplication();
        $app->handle($request);
    }

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testAppFinishedEventTriggerWhenAppNotCatchingException($exception) {
        $request = Request::create('http://labrador.dev');
        $this->router->expects($this->once())
            ->method('match')
            ->will($this->throwException($exception));

        // The 0 call is AppHandleEvent
        $this->eventDispatcher->expects($this->at(1))
                              ->method('dispatch')
                              ->with(
                                  Events::APP_FINISHED,
                                  $this->callback(function($arg) {
                                      return $arg instanceof Events\ApplicationFinishedEvent;
                                  })
                                );

        $app = $this->createApplication();
        try {
            $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
        } catch(PhpException $exc) {
            // don't want tests to fail because we know the application will throw an exception
        }
    }

}
