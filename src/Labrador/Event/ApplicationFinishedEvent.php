<?php

/**
 * An event triggered after the Labrador\Application is finished processing the
 * request and before the Response is sent to the user.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Event;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ApplicationFinishedEvent extends LabradorEvent {

    /**
     *
     * @param RequestStack $requestStack
     * @param Response $response
     */
    function __construct(RequestStack $requestStack, Response $response) {
        parent::__construct($requestStack);
        if ($response) {
            $this->setResponse($response);
        }
    }

} 
