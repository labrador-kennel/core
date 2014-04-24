<?php

/**
 * Acts as primary processing for the Labrador library.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Labrador\Router\Router;
use Labrador\Router\HandlerResolver;
use Labrador\Exception\HttpException;
use Labrador\Exception\InvalidHandlerException;
use Labrador\Exception\ServerErrorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Exception as PhpException;

class Application implements HttpKernelInterface {

    const CATCH_EXCEPTIONS = true;
    const DO_NOT_CATCH_EXCEPTIONS = false;

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
     */
    function __construct(Router $router, HandlerResolver $resolver) {
        $this->resolver = $resolver;
        $this->router = $router;
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
            $handler = $this->router->match($request);
            $cb = $this->resolver->resolve($handler);
            $response = $cb($request);
            if (!$response instanceof Response) {
                throw new ServerErrorException('Controller actions MUST return an instance of Symfony\\Component\\HttpFoundation\\Response');
            }
        } catch (HttpException $httpExc) {
            if (!$catch) { throw $httpExc; }
            $response = new Response($httpExc->getMessage(), $httpExc->getCode());
        } catch (InvalidHandlerException $handlerExc) {
            if (!$catch) { throw $handlerExc; }
            $response = new Response('Fatal error creating the requested handler', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (PhpException $phpExc) {
            $response = new Response($phpExc->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

}
