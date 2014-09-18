<?php

/**
 * Base event that allows all Labrador triggered events to have access to the
 * RequestStack being used for processing.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

abstract class LabradorEvent extends Event {

    private $requestStack;
    private $response;

    /**
     * @param RequestStack $requestStack
     */
    function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    function getMasterRequest() {
        return $this->requestStack->getMasterRequest();
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    function getCurrentRequest() {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return bool
     */
    function isMasterRequest() {
        return $this->getMasterRequest() === $this->getCurrentRequest();
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    function getResponse() {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    function setResponse(Response $response) {
        $this->response = $response;
    }

}
