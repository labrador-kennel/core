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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Exception as PhpException;

/**
 *
 */
class Application implements HttpKernelInterface {

    const CATCH_EXCEPTIONS = true;
    const DO_NOT_CATCH_EXCEPTIONS = false;

    private $eventDispatcher;

    /**
     * @property HandlerResolver
     */
    private $resolver;

    /**
     * @property Router
     */
    private $router;

    /**
     * @param Router $router
     * @param HandlerResolver $resolver
     * @param EventDispatcherInterface $eventDispatcher
     */
    function __construct(Router $router, HandlerResolver $resolver, EventDispatcherInterface $eventDispatcher) {
        $this->router = $router;
        $this->resolver = $resolver;
        $this->eventDispatcher = $eventDispatcher;
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
            $response = $this->triggerHandleEvent($request);
            if (!$response) {
                $cb = $this->triggerRouteFoundEvent($request);
                $response = $this->executeController($request, $cb);
            }
            $response = $this->triggerApplicationFinishedEvent($request, $response);
        } catch (HttpException $httpExc) {
            if (!$catch) { throw $httpExc; }
            $response = new Response($httpExc->getMessage(), $httpExc->getCode());
            $event = new ExceptionThrownEvent($request, $response, $httpExc);
            $this->eventDispatcher->dispatch(Events::EXCEPTION_THROWN, $event);
        } catch (PhpException $phpExc) {
            if (!$catch) { throw $phpExc; }
            $response = new Response($phpExc->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            $event = new ExceptionThrownEvent($request, $response, $phpExc);
            $this->eventDispatcher->dispatch(Events::EXCEPTION_THROWN, $event);
        }

        return $response;
    }

    private function triggerHandleEvent(Request $request) {
        $event = new ApplicationHandleEvent($request);
        $this->eventDispatcher->dispatch(Events::APP_HANDLE_EVENT, $event);
        return $event->getResponse();
    }

    private function triggerRouteFoundEvent(Request $request) {
        $handler = $this->router->match($request);
        $cb = $this->resolver->resolve($handler);
        $event = new RouteFoundEvent($request, $cb);
        $this->eventDispatcher->dispatch(Events::ROUTE_FOUND_EVENT, $event);
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

    private function triggerApplicationFinishedEvent(Request $request, Response $response) {
        $event = new ApplicationFinishedEvent($request, $response);
        $this->eventDispatcher->dispatch(Events::APP_FINISHED_EVENT, $event);

        return $response;
    }

}
