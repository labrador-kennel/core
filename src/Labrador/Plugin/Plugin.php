<?php

/**
 * Objects that interact with the Labrador engine, the application running on top
 * of Labrador, or extensions to other plugins.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

interface Plugin {

    /**
     * Return the name of the plugin; this name should match /[A-Za-z0-9\.\-\_]/
     *
     * @return string
     */
    public function getName();

    /**
     * Perform any actions that should be
     */
    public function boot();

} 
