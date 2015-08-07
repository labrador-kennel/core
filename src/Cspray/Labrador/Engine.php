<?php

declare(strict_types=1);

/**
 * An interface that represents primary execution logic for a Labrador powered application.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Plugin\Pluggable;

interface Engine extends Pluggable {

    // These are the bare MINIMUM amount of events that an engine should trigger
    // An Engine MAY trigger more events but at least these should be
    const ENVIRONMENT_INITIALIZE_EVENT = 'labrador.environment_initialize';
    const APP_EXECUTE_EVENT = 'labrador.application_execute';
    const APP_CLEANUP_EVENT = 'labrador.app_cleanup';
    const EXCEPTION_THROWN_EVENT = 'labrador.exception_thrown';

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * Return the version of the Engine; this should be in semver format.
     *
     * @return string
     */
    public function getVersion() : string;

    /**
     * @return mixed
     */
    public function run();

}