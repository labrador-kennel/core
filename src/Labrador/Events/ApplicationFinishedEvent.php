<?php

/**
 * An event triggered after the Labrador\Application is finished processing the
 * request and before the Response is sent to the user.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationFinishedEvent extends LabradorEvent {

    private $response;

    function __construct(Request $request, Response $response) {
        parent::__construct($request);
        $this->response = $response;
    }

    function getResponse() {
        return $this->response;
    }

    function setResponse(Response $response) {
        $this->response = $response;
    }

} 
