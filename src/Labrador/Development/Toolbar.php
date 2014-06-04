<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Development;

use Labrador\Events\ApplicationFinishedEvent;
use Labrador\Events\ApplicationHandleEvent;
use Labrador\Events\RouteFoundEvent;
use Labrador\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class Toolbar {

    private $eventDispatcher;
    protected $runtimeProfiler;
    protected $request;

    function __construct(EventDispatcherInterface $eventDispatcher, RuntimeProfiler $runtimeProfiler, Request $request) {
        $this->eventDispatcher = $eventDispatcher;
        $this->runtimeProfiler = $runtimeProfiler;
        $this->request = $request;
    }

    function registerEventListeners() {
        $this->eventDispatcher->addListener(Events::APP_FINISHED, [$this, 'appFinishedEvent']);
    }

    function appFinishedEvent(ApplicationFinishedEvent $event) {
        $this->runtimeProfiler->setAppFinished();
    }

} 
