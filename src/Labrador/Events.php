<?php

/**
 * The various events that Labrador will trigger.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador;

abstract class Events {

    const APP_HANDLE = 'labrador.app_handle';
    const BEFORE_CONTROLLER = 'labrador.before_controller';
    const AFTER_CONTROLLER = 'labrador.after_controller';
    const APP_FINISHED = 'labrador.app_finished';
    const EXCEPTION_THROWN = 'labrador.exception_thrown';

} 
