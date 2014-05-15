<?php

/**
 * An event triggered at the beginning of a Labrador\Application handling a
 * request.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Symfony\Component\HttpFoundation\Response;

class ApplicationHandleEvent extends LabradorEvent {

    private $response;

    /**
     * @return Response|null
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
