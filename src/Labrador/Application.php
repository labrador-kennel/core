<?php

/**
 * Acts as primary processing for the Labrador library.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Labrador\Events\ApplicationFinishedEvent;
use Labrador\Events\ApplicationHandleEvent;
use Labrador\Events\ExceptionThrownEvent;
use Labrador\Events\RouteFoundEvent;
use Labrador\Router\Router;
use Labrador\Router\HandlerResolver;
use Labrador\Exception\HttpException;
use Labrador\Exception\ServerErrorException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Exception as PhpException;

/**
 * While this is the primary processing logic we have designed it in such a way
 * that you should be able to easily replace dependencies and extend or change
 * Labrador's behavior very easily in the vast majority of use cases.
 *
 * This implementation primarily provides that flexibility in 1 of 2 ways; through
 * interface driven dependencies and timely event triggering.
 *
 * All of the Application dependencies are requested as a particular interface.
 * This allows you to change out implementations at will; assuming that each of
 * those implementations adheres to the described interface.
 *
 * Events are a much more flexible, easy-to-use way to make Labrador do what you
 * need it to do. By providing a few, strategically timed events you have the ability
 * to do a lot of nifty things. We're gonna take a look at the events triggered
 * when Application::handle is run.
 *
 * Events::APP_HANDLE = labrador.app_handle     Labrador\Events\ApplicationHandleEvent
 * -----------------------------------------------------------------------------
 * Triggered once every time Application::handle is called. If a Response is returned
 * from ApplicationHandleEvent::getResponse then Labrador will short circuit
 * normal processing; meaning no controller, if there would be one routed for the
 * Request, will be created or invoked. Additionally, Events::ROUTE_FOUND will
 * not be triggered.
 *
 * Events::ROUTE_FOUND = labrador.route_found   Labrador\Events\RouteFoundEvent
 * -----------------------------------------------------------------------------
 * Triggered if a Request was successfully routed and resolved into a callable
 * function. The callback returned from RouteFoundEvent::getController will be
 * the controller invoked for the given Request. By default the resolved controller
 * is returned from this method; you would need to explicitly call RouteFoundEvent::setController.
 *
 * Events::APP_FINISHED = labrador.app_finished     Labrador\Events\ApplicationFinishedEvent
 * -----------------------------------------------------------------------------
 * Triggered when the Application is finished handling the Request. You can return a
 * Response with ApplicaitonFinishedEvent::getResponse that will be used in place
 * of the one returned from the controller.
 *
 * Events::EXCEPTION_THROWN = labrador.exception_thrown     Labrador\Events\ExceptionThrownEvent
 * -----------------------------------------------------------------------------
 * Triggered if an exception is caught by the Application. You can set a Response
 * in this event to change the generic Response set by the Application.
 */
class Application implements HttpKernelInterface {

    const CATCH_EXCEPTIONS = true;
    const THROW_EXCEPTIONS = false;

    private $eventDispatcher;

    /**
     * @property HandlerResolver
     */
    private $resolver;

    /**
     * @property Router
     */
    private $router;

    private $requestStack;

    /**
     * @param Router $router
     * @param HandlerResolver $resolver
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     */
    function __construct(Router $router, HandlerResolver $resolver, EventDispatcherInterface $eventDispatcher, RequestStack $requestStack) {
        $this->router = $router;
        $this->resolver = $resolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
    }

    function getRouter() {
        return $this->router;
    }

    function onHandle(callable $function, $priority = 0) {
        $this->eventDispatcher->addListener(Events::APP_HANDLE, $function, $priority);
        return $this;
    }

    function onFinished(callable $function, $priority = 0) {
        $this->eventDispatcher->addListener(Events::APP_FINISHED, $function, $priority);
        return $this;
    }

    function onRouteFound(callable $function, $priority = 0) {
        $this->eventDispatcher->addListener(Events::ROUTE_FOUND, $function, $priority);
        return $this;
    }

    function onException(callable $function, $priority = 0) {
        $this->eventDispatcher->addListener(Events::EXCEPTION_THROWN, $function, $priority);
        return $this;
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param integer $type The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param Boolean $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    function handle(Request $request, $type = self::MASTER_REQUEST, $catch = self::CATCH_EXCEPTIONS) {
        try {
            $this->requestStack->push($request);
            $response = $this->triggerHandleEvent();
            if (!$response) {
                $cb = $this->triggerRouteFoundEvent($request);
                $response = $this->executeController($request, $cb);
            }
            $response = $this->triggerApplicationFinishedEvent($response);
        } catch (PhpException $exc) {
            $code = ($exc instanceof HttpException) ? $exc->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

            if (!$catch) {
                $response = isset($response) ? $response : null;
                $this->triggerApplicationFinishedEvent($response);
                throw $exc;
            }
            $response = $this->handleCaughtException($exc, $code);
            $response = $this->triggerApplicationFinishedEvent($response);
        }

        return $response;
    }

    private function triggerHandleEvent() {
        $event = new ApplicationHandleEvent($this->requestStack);
        $this->eventDispatcher->dispatch(Events::APP_HANDLE, $event);
        return $event->getResponse();
    }

    private function triggerRouteFoundEvent(Request $request) {
        $handler = $this->router->match($request);
        $cb = $this->resolver->resolve($handler);
        if (!is_callable($cb)) {
            $msg = 'An error was encountered resolving a found handler matching %s %s';
            throw new ServerErrorException(sprintf($msg, $request->getMethod(), $request->getPathInfo()));
        }
        $event = new RouteFoundEvent($this->requestStack, $cb);
        $this->eventDispatcher->dispatch(Events::ROUTE_FOUND, $event);
        return $event->getController();
    }

    private function executeController(Request $request, callable $cb) {
        $response = $cb($request);
        if (!$response instanceof Response) {
            $msg = 'Controllers MUST return an instance of Symfony\\Component\\HttpFoundation\\Response.';
            $msg .= ' The "%s" handler returned type (%s).';
            throw new ServerErrorException(sprintf($msg, $request->attributes->get('_labrador')['handler'], gettype($response)));
        }

        return $response;
    }

    private function triggerApplicationFinishedEvent(Response $response = null) {
        $event = new ApplicationFinishedEvent($this->requestStack, $response);
        $this->eventDispatcher->dispatch(Events::APP_FINISHED, $event);
        $response = $event->getResponse();
        $this->requestStack->pop();
        return $response;
    }

    private function handleCaughtException(PhpException $exception, $httpStatus) {
        $response = new Response($exception->getMessage(), $httpStatus);
        $event = new ExceptionThrownEvent($this->requestStack, $response, $exception);
        $this->eventDispatcher->dispatch(Events::EXCEPTION_THROWN, $event);
        return $event->getResponse();
    }

}
