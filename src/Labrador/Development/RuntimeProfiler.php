<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Development;

use Symfony\Component\HttpFoundation\Request;

class RuntimeProfiler {

    private $request;
    private $requestStartTime;
    private $appFinishTime;


    function __construct(Request $request) {
        $this->request = $request;
        $this->requestStartTime = $request->server->get('REQUEST_TIME_FLOAT');
    }

    function setAppFinished() {
        $this->appFinishTime = microtime(true);
    }

    function getRequest() {
        return $this->request;
    }

    function getTotalTimeElapsed() {
        return $this->appFinishTime - $this->requestStartTime;
    }

    function getPeakMemoryUsage() {
        $memory = memory_get_peak_usage(false);
        return $memory / 1024 / 1024;
    }





} 
