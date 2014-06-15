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
use Symfony\Component\HttpFoundation\RequestStack;

abstract class LabradorEvent extends Event {

    protected $requestStack;

    function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    function getMasterRequest() {
        return $this->requestStack->getMasterRequest();
    }

    function getCurrentRequest() {
        return $this->getCurrentRequest();
    }

    function isMasterRequest() {
        return $this->getMasterRequest() === $this->getCurrentRequest();
    }

}
