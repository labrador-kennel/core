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
use Labrador\Event;
use Labrador\Events as LabradorEvents;
use Labrador\Exception\InvalidTypeException;
use Labrador\Router\ResolvedRoute;
use Labrador\Test\Stub\RouterStub;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit_Framework_TestCase as UnitTestCase;
use Exception as PhpException;

class ApplicationTest extends UnitTestCase {

    function testGettingRouter() {
        $router = new RouterStub();
        $app = new Application($router);
        $this->assertSame($router, $app->getRouter());
    }

    function testControllerMustReturnResponse() {
        $request = Request::create('http://www.labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { return 'handler#action'; }, Response::HTTP_OK);
        $router = new RouterStub($resolvedRoute);
        $app = new Application($router);

        $response = $app->handle($request);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Response', $response);
        $this->assertSame(500, $response->getStatusCode());
        $msg = 'Controllers MUST return an instance of Symfony\\Component\\HttpFoundation\\Response.';
        $msg .= ' The controller returned type (string).';
        $this->assertSame($msg, $response->getContent());
    }

    function eventProvider() {
        return [
            [0, LabradorEvents::APP_HANDLE, 'Labrador\\Event\\ApplicationHandleEvent'],
            [1, LabradorEvents::BEFORE_CONTROLLER, 'Labrador\\Event\\BeforeControllerEvent'],
            [2, LabradorEvents::AFTER_CONTROLLER, 'Labrador\\Event\\AfterControllerEvent'],
            [3, LabradorEvents::APP_FINISHED, 'Labrador\\Event\\ApplicationFinishedEvent']
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
        $router = new RouterStub($resolved);
        $eventDispatcher = $this->getMock(EventDispatcher::class);

        $eventDispatcher->expects($this->at($triggerIndex))
                        ->method('dispatch')
                        ->with(
                            $eventName,
                            $this->callback(function($arg) use($eventClass) {
                                return $arg instanceof $eventClass;
                            })
                        );

        $app = new Application($router, $eventDispatcher);
        $app->handle($request);
    }

    function responseEventProvider() {
        return [
            ['onHandle'],
            ['onBeforeController', new ResolvedRoute(new Request(), function() { throw new PhpException('Should never be called'); }, Response::HTTP_OK)],
            ['onAfterController', new ResolvedRoute(new Request(), function() { return new Response(''); }, Response::HTTP_OK)],
            ['onFinished', new ResolvedRoute(new Request(), function() { return new Response(''); }, Response::HTTP_OK)],
            ['onException', new ResolvedRoute(new Request(), function() { throw new PhpException(); }, Response::HTTP_OK)]
        ];
    }

    /**
     * @dataProvider responseEventProvider
     */
    function testResponseSetInEventReturned($appMiddlewareMethod, ResolvedRoute $resolvedRoute = null) {
        $request = Request::create('http://labrador.dev');
        $router = new RouterStub($resolvedRoute);
        $eventDispatcher = new EventDispatcher();
        $app = new Application($router, $eventDispatcher);
        $app->$appMiddlewareMethod(function($event) {
            $event->setResponse(new Response('Called from event'));
        });

        $response = $app->handle($request);
        $this->assertSame('Called from event', $response->getContent());
    }

    function testApplicationFinishedTriggeredDuringNormalProcessing() {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { return new Response(''); }, Response::HTTP_OK);
        $router = new RouterStub($resolvedRoute);

        $app = new Application($router);
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
        $router = new RouterStub($resolvedRoute);

        $app = new Application($router);
        $msg = 'Controllers MUST return an instance of Symfony\\Component\\HttpFoundation\\Response.';
        $msg .= ' The controller returned type (string).';
        $this->setExpectedException(InvalidTypeException::class, $msg);
        $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
    }

    function testResponseFromNotOkRouteIsReturned() {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { return new Response('This resource could not be found'); }, Response::HTTP_NOT_FOUND);
        $router = new RouterStub($resolvedRoute);

        $app = new Application($router);
        $response = $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
        $this->assertSame('This resource could not be found', $response->getContent());
    }

    function exceptionThrownProvider() {
        return [
            [new PhpException('this is the message why something failed')]
        ];
    }

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testThrowExceptionIfNotHandling(PhpException $exception) {
        $request = Request::create('http://www.labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() use($exception) { throw $exception; }, Response::HTTP_OK);
        $router = new RouterStub($resolvedRoute);
        $app = new Application($router);

        $this->setExpectedException(get_class($exception), $exception->getMessage());
        $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
    }

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testAppFinishedEventTriggeredWhenExceptionCaught($exception) {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() use($exception) { throw $exception; }, Response::HTTP_OK);
        $router = new RouterStub($resolvedRoute);
        $app = new Application($router);
        $triggered = false;
        $app->onFinished(function() use(&$triggered) {
            $triggered = true;
        });
        $app->handle($request);

        $this->assertTrue($triggered);
    }

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testAppFinishedEventTriggerWhenAppNotCatchingException($exception) {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() use($exception) { throw $exception; }, Response::HTTP_OK);
        $router = new RouterStub($resolvedRoute);
        $app = new Application($router);
        $triggered = false;
        $app->onFinished(function() use(&$triggered) {
            $triggered = true;
        });
        try {
            $app->handle($request, Application::MASTER_REQUEST, Application::THROW_EXCEPTIONS);
        } catch(PhpException $exc) {
            // no op
        } finally {
            $this->assertTrue($triggered);
        }
    }

    /**
     * @dataProvider exceptionThrownProvider
     */
    function testExceptionAccessibleFromExceptionEvent($exception) {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() use($exception) { throw $exception; }, Response::HTTP_OK);
        $router = new RouterStub($resolvedRoute);
        $app = new Application($router);
        $exceptionInstance = null;
        $app->onException(function($event) use(&$exceptionInstance) {
            $exceptionInstance = $event->getException();
        });
        $app->handle($request);
        $this->assertSame($exception, $exceptionInstance);
    }

    function testChangingControllerInBeforeControllerMiddleware() {
        $request = Request::create('http://labrador.dev');
        $resolvedRoute = new ResolvedRoute($request, function() { return new Response('from route'); }, Response::HTTP_OK);
        $router = new RouterStub($resolvedRoute);
        $app = new Application($router);

        $app->onBeforeController(function(Event\BeforeControllerEvent $event) {
            $controller = $event->getController();
            $decorator = function() use($controller) {
                $response = $controller();
                return new Response($response->getContent() . ' and decorator');
            };
            $event->setController($decorator);
        });

        $response = $app->handle($request);
        $this->assertSame('from route and decorator', $response->getContent());
    }

}
