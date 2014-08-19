<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;


use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class AfterControllerEvent extends LabradorEvent {

    private $response;

    function __construct(RequestStack $requestStack, Response $response) {
        parent::__construct($requestStack);
        $this->response = $response;
    }

    function getResponse() {
        return $this->response;
    }

    function setResponse(Response $response) {
        $this->response = $response;
    }


} 
