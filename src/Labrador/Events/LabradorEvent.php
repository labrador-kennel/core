<?php

/**
 * Base event that allows all Labrador triggered events to have access to the
 * HTTP request the event is being triggered for.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

abstract class LabradorEvent extends Event {

    private $request;

    function __construct(Request $request) {
        $this->request = $request;
    }

    function getRequest() {
        return $this->request;
    }

} 
