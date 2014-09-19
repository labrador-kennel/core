<?php

/**
 * Event triggered after the controller for a given request has been invoked.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Event;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class AfterControllerEvent extends LabradorEvent {

    /**
     * @param RequestStack $requestStack
     * @param Response $response
     */
    function __construct(RequestStack $requestStack, Response $response) {
        parent::__construct($requestStack);
        $this->setResponse($response);
    }




} 
