<?php

/**
 * Acts as primary processing for the Labrador library.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Labrador\Events\AfterControllerEvent;
use Labrador\Events\ApplicationFinishedEvent;
use Labrador\Events\ApplicationHandleEvent;
use Labrador\Events\ExceptionThrownEvent;
use Labrador\Events\BeforeControllerEvent;
use Labrador\Router\ResolvedRoute;
use Labrador\Router\Router;
use Labrador\Exception\HttpException;
use Labrador\Exception\ServerErrorException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Exception as PhpException;

class Application implements HttpKernelInterface {

    const CATCH_EXCEPTIONS = true;
    const THROW_EXCEPTIONS = false;

    private $eventDispatcher;
    private $router;
    private $requestStack;

    /**
     * @param Router $router
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     */
    function __construct(Router $router, EventDispatcherInterface $eventDispatcher, RequestStack $requestStack) {
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * @return Router
     */
    function getRouter() {
        return $this->router;
    }

    /**
     * @param callable $function
     * @param int $priority
     * @return $this
     */
    function onHandle(callable $function, $priority = 0) {
        $this->eventDispatcher->addListener(Events::APP_HANDLE, $function, $priority);
        return $this;
    }

    /**
     * @param callable $function
     * @param int $priority
     * @return $this
     */
    function onFinished(callable $function, $priority = 0) {
        $this->eventDispatcher->addListener(Events::APP_FINISHED, $function, $priority);
        return $this;
    }

    /**
     * @param callable $function
     * @param int $priority
     * @return $this
     */
    function onBeforeController(callable $function, $priority = 0) {
        $this->eventDispatcher->addListener(Events::BEFORE_CONTROLLER, $function, $priority);
        return $this;
    }

    /**
     * @param callable $function
     * @param int $priority
     * @return $this
     */
    function onAfterController(callable $function, $priority = 0) {
        $this->eventDispatcher->addListener(Events::AFTER_CONTROLLER, $function, $priority);
        return $this;
    }

    /**
     * @param callable $function
     * @param int $priority
     * @return $this
     */
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
     * @param boolean $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing and not configured to CATCH_EXCEPTIONS
     */
    function handle(Request $request, $type = self::MASTER_REQUEST, $catch = self::CATCH_EXCEPTIONS) {
        try {
            $this->requestStack->push($request);
            $response = $this->triggerHandleEvent();
            if (!$response) {
                $response = $this->executeControllerProcessing($request);
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

    private function executeControllerProcessing(Request $request) {
        $resolved = $this->router->match($request);
        if (!$resolved->isOk()) {
            $controller = $resolved->getController();
            $response = $controller($request);
            if (!$response instanceof Response) {
                $msg = 'Controllers MUST return an instance of %s. The controller returned type (%s).';
                throw new ServerErrorException(sprintf($msg, Response::class, gettype($response)));
            }

            return $response;
        }

        $event = $this->triggerBeforeControllerEvent($resolved);
        if ($event->getResponse()) {
            return $event->getResponse();
        }

        $response = $this->executeController($request, $resolved->getController());
        return $this->triggerAfterControllerEvent($response);
    }

    private function triggerBeforeControllerEvent(ResolvedRoute $resolvedRoute) {
        $event = new BeforeControllerEvent($this->requestStack, $resolvedRoute);
        $this->eventDispatcher->dispatch(Events::BEFORE_CONTROLLER, $event);
        return $event;
    }

    private function executeController(Request $request, callable $cb) {
        $response = $cb($request);
        if (!$response instanceof Response) {
            $msg = 'Controllers MUST return an instance of %s. The controller returned type (%s).';
            throw new ServerErrorException(sprintf($msg, Response::class, gettype($response)));
        }

        return $response;
    }

    private function triggerAfterControllerEvent(Response $response) {
        $event = new AfterControllerEvent($this->requestStack, $response);
        $this->eventDispatcher->dispatch(Events::AFTER_CONTROLLER, $event);
        return $event->getResponse();
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
