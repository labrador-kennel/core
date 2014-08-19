<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvedRoute {

    private $httpStatus;
    private $request;
    private $controller;
    private $availableMethods;

    function __construct(Request $requet, callable $controller, $httpStatus, array $availableMethods = []) {
        $this->request = $requet;
        $this->controller = $controller;
        $this->httpStatus = $httpStatus;
        $this->availableMethods = $availableMethods;
    }

    function getRequest() {
        return $this->request;
    }

    function getController() {
        return $this->controller;
    }

    function isOk() {
        return $this->httpStatus === Response::HTTP_OK;
    }

    function isNotFound() {
        return $this->httpStatus === Response::HTTP_NOT_FOUND;
    }

    function isMethodNotAllowed() {
        return $this->httpStatus === Response::HTTP_METHOD_NOT_ALLOWED;
    }

    function getAvailableMethods() {
        return $this->availableMethods;
    }



} 
