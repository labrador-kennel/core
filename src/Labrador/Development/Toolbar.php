<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Development;

use Labrador\Events\ApplicationFinishedEvent;
use Labrador\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class Toolbar {

    private $eventDispatcher;
    protected $runtimeProfiler;
    protected $requestStack;
    protected $gitBranch;

    function __construct(EventDispatcherInterface $eventDispatcher, RuntimeProfiler $runtimeProfiler, RequestStack $requestStack, GitBranch $gitBranch) {
        $this->eventDispatcher = $eventDispatcher;
        $this->runtimeProfiler = $runtimeProfiler;
        $this->requestStack = $requestStack;
        $this->gitBranch = $gitBranch;
    }

    function registerEventListeners() {
        $this->eventDispatcher->addListener(Events::APP_FINISHED, [$this, 'appFinishedEvent']);
    }

    function appFinishedEvent(ApplicationFinishedEvent $event) {
        $this->runtimeProfiler->setAppFinished();
    }

} 
