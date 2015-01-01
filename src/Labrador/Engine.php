<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

use Labrador\Plugin\Pluggable;

interface Engine extends Pluggable {

    const PLUGIN_BOOT_EVENT = 'labrador.plugin_boot';
    const APP_EXECUTE_EVENT = 'labrador.application_execute';
    const PLUGIN_CLEANUP_EVENT = 'labrador.plugin_cleanup';
    const EXCEPTION_THROWN_EVENT = 'labrador.exception_thrown';

    /**
     * @return string
     */
    public function getName();

    /**
     * Return the version of the Engine; this should be in the semver format.
     *
     * @return string
     */
    public function getVersion();

    /**
     * @return mixed
     */
    public function run();

} 
