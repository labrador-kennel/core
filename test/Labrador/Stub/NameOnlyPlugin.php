<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Stub;


use Labrador\Plugin\Plugin;

class NameOnlyPlugin implements Plugin {

    private $name;

    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Return the name of the plugin; this should be a string that could be used
     * as a PHP object property.
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    public function boot() {

    }

}
