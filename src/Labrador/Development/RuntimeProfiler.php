<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Development;

use Symfony\Component\HttpFoundation\RequestStack;

class RuntimeProfiler {

    private $requestStack;
    private $appFinishTime;


    function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;

    }

    function setAppFinished() {
        $this->appFinishTime = microtime(true);
    }

    function getMasterRequest() {
        return $this->requestStack->getMasterRequest();
    }

    function getTotalTimeElapsed() {
        return $this->appFinishTime - $this->getMasterRequest()->server->get('REQUEST_TIME_FLOAT');
    }

    function getPeakMemoryUsage() {
        $memory = memory_get_peak_usage(false);
        return $memory / 1024 / 1024;
    }

}
