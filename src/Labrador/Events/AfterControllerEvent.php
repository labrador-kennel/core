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


    function __construct(RequestStack $requestStack, Response $response) {
        parent::__construct($requestStack);
        $this->setResponse($response);
    }




} 
