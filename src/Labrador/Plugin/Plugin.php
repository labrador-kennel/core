<?php

/**
 * Objects that interact with the Labrador engine or an application written on top
 * of Labrador; primarily this will involve making use of the EventAwarePlugin and
 * ServiceAwarePlugin interfaces to respond to triggered events or provide services.
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
