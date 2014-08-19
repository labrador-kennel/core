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

    function testGettingRouter() {
        $app = $this->createApplication();
        $this->assertSame($this->router, $app->getRouter());
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
        $msg .= ' The controller returned type (string).';
        $this->assertSame($msg, $response->getContent());
    }

    function eventProvider() {
        return [
            [0, Events::APP_HANDLE, 'Labrador\\Events\\ApplicationHandleEvent'],
            [1, Events::BEFORE_CONTROLLER, 'Labrador\\Events\\BeforeControllerEvent'],
            [2, Events::AFTER_CONTROLLER, 'Labrador\\Events\\AfterControllerEvent'],
            [3, Events::APP_FINISHED, 'Labrador\\Events\\ApplicationFinishedEvent']
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

    function testResponseSetInAppHandleEventShortCircuits() {
        $request = Request::create('http://labrador.dev');

        $this->router->expects($this->never())->method('match');

        $eventDispatcher = new EventDispatcher();
        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $app->onHandle(function(Events\ApplicationHandleEvent $event) {
            $event->setResponse(new Response('Called from event'));
        });

        $response = $app->handle($request);
        $this->assertSame('Called from event', $response->getContent());
    }

    function testResponseSetInBeforeControllerShortCircuits() {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { throw new PhpException('Should never be called'); }, Response::HTTP_OK);
        $this->router->expects($this->once())->method('match')->with($request)->willReturn($resolvedRoute);

        $eventDispatcher = new EventDispatcher();
        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $app->onBeforeController(function(Events\BeforeControllerEvent $event) {
            $event->setResponse(new Response('called from event'));
        });

        $response = $app->handle($request);
        $this->assertSame('called from event', $response->getContent());
    }

    function testResponseSetInAfterControllerIsReturned() {
        $request = Request::create('http://labrador.dev');
        $resolved = new ResolvedRoute($request, function() { return new Response(''); }, Response::HTTP_OK);

        $this->router->expects($this->once())->method('match')->will($this->returnValue($resolved));

        $eventDispatcher = new EventDispatcher();
        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $app->onAfterController(function(Events\AfterControllerEvent $event) {
            $event->setResponse(new Response('called from the after_controller listener'));
        });

        $response = $app->handle($request);
        $this->assertSame('called from the after_controller listener', $response->getContent());
    }

    function testResponseSetInAppFinishedEventIsReturned() {
        $request = Request::create('http://labrador.dev');
        $resolved = new ResolvedRoute($request, function() { return new Response(''); }, Response::HTTP_OK);

        $this->router->expects($this->once())->method('match')->will($this->returnValue($resolved));

        $eventDispatcher = new EventDispatcher();
        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $app->onFinished(function(Events\ApplicationFinishedEvent $event) {
            $event->setResponse(new Response('called from the app_finished listener'));
        });

        $response = $app->handle($request);
        $this->assertSame('called from the app_finished listener', $response->getContent());
    }

    function testApplicationFinishedTriggeredDuringNormalProcessing() {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { return new Response(''); }, Response::HTTP_OK);
        $this->router->method('match')->willReturn($resolvedRoute);

        $eventDispatcher = new EventDispatcher();
        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $finishCalled = false;
        $app->onFinished(function() use(&$finishCalled) {
            $finishCalled = true;
        });
        $app->handle($request);

        $this->assertTrue($finishCalled);
    }

    function testResponseFromNotOkRouteMustBeResponse() {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { return 'not a response object'; }, Response::HTTP_NOT_FOUND);
        $this->router->expects($this->once())->method('match')->willReturn($resolvedRoute);

        $app = new Application($this->router, $this->eventDispatcher, $this->requestStack);
        $msg = 'Controllers MUST return an instance of Symfony\\Component\\HttpFoundation\\Response.';
        $msg .= ' The controller returned type (string).';
        $this->setExpectedException(ServerErrorException::class, $msg);
        $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
    }

    function testResponseFromNotOkRouteIsReturned() {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { return new Response('This resource could not be found'); }, Response::HTTP_NOT_FOUND);
        $this->router->expects($this->once())->method('match')->willReturn($resolvedRoute);

        $app = new Application($this->router, $this->eventDispatcher, $this->requestStack);
        $response = $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
        $this->assertSame('This resource could not be found', $response->getContent());
    }

    function exceptionThrownProvider() {
        return [
            [new PhpException('this is the message why something failed')],
            [new NotFoundException('this is the resource not found')]
        ];
    }

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testThrowExceptionIfNotHandling(PhpException $exception) {
        $request = Request::create('http://www.labrador.dev');

        $this->router->expects($this->once())
            ->method('match')
            ->with($request)
            ->will($this->throwException($exception));

        $app = $this->createApplication();
        $this->setExpectedException(get_class($exception), $exception->getMessage());
        $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
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

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testResponseSetInExceptionThrownEventIsReturned($exception) {
        $request = Request::create('http://labrador.dev');
        $this->router->expects($this->once())
            ->method('match')
            ->will($this->throwException($exception));

        $eventDispatcher = new EventDispatcher();
        $app = new Application($this->router, $eventDispatcher, $this->requestStack);
        $app->onException(function(Events\ExceptionThrownEvent $event) {
            $event->setResponse(new Response('Called from exception thrown listener'));
        });

        $response = $app->handle($request);
        $this->assertSame('Called from exception thrown listener', $response->getContent());
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
