<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Router;

class Route {

    private $pattern;
    private $method;
    private $handler;

    function __construct($pattern, $method, $handler) {
        $this->pattern = $pattern;
        $this->method = $method;
        $this->handler = $handler;
    }

    function getPattern() {
        return $this->pattern;
    }

    function getMethod() {
        return $this->method;
    }

    function getHandler() {
        return $this->handler;
    }

    function __toString() {
        $format = "%s\t%s\t\t%s";
        $handler = $this->getNormalizedHandler($this->handler);
        return sprintf($format, $this->method, $this->pattern, $handler);
    }

    private function getNormalizedHandler($handler) {
        if ($handler instanceof \Closure) {
            return 'closure{}';
        }

        if (is_object($handler)) {
            return get_class($handler);
        }

        if (is_array($handler)) {
            if (is_callable($handler)) {
                return get_class($handler[0]) . '::' . $handler[1];
            }

            return 'Array(' . count($handler) . ')';
        }

        return $handler;
    }

} 
