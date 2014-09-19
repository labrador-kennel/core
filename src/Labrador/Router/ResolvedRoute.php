<?php

/**
 * The result of a call to Router::match that stores the Request being matched
 * against and the result for whether the Router found a matching and resolved
 * controller.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Router;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvedRoute {

    private $httpStatus;
    private $request;
    private $controller;
    private $availableMethods;

    /**
     * @param Request $request
     * @param callable $controller
     * @param $httpStatus
     * @param array $availableMethods
     */
    function __construct(Request $request, callable $controller, $httpStatus, array $availableMethods = []) {
        $this->request = $request;
        $this->controller = $controller;
        $this->httpStatus = $httpStatus;
        $this->availableMethods = $availableMethods;
    }

    /**
     * @return Request
     */
    function getRequest() {
        return $this->request;
    }

    /**
     * @return callable
     */
    function getController() {
        return $this->controller;
    }

    /**
     * @return bool
     */
    function isOk() {
        return $this->httpStatus === Response::HTTP_OK;
    }

    /**
     * @return bool
     */
    function isNotFound() {
        return $this->httpStatus === Response::HTTP_NOT_FOUND;
    }

    /**
     * @return bool
     */
    function isMethodNotAllowed() {
        return $this->httpStatus === Response::HTTP_METHOD_NOT_ALLOWED;
    }

    /**
     * @return array
     */
    function getAvailableMethods() {
        return $this->availableMethods;
    }



} 
