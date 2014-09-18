<?php

/**
 * An object that represents what HTTP Request data should be mapped to which handler.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador\Router;

class Route {

    private $pattern;
    private $method;
    private $handler;

    /**
     * @param string $pattern
     * @param string $method
     * @param mixed $handler
     */
    function __construct($pattern, $method, $handler) {
        $this->pattern = $pattern;
        $this->method = $method;
        $this->handler = $handler;
    }

    /**
     * @return string
     */
    function getPattern() {
        return $this->pattern;
    }

    /**
     * @return string
     */
    function getMethod() {
        return $this->method;
    }

    /**
     * @return mixed
     */
    function getHandler() {
        return $this->handler;
    }

    /**
     * @return string
     */
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
