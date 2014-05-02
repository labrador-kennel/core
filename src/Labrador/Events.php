<?php

/**
 * The various events that Labrador will trigger.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador;

abstract class Events {

    const APP_HANDLE_EVENT = 'labrador.app_handle';
    const ROUTE_FOUND_EVENT= 'labrador.route_found';
    const APP_FINISHED_EVENT = 'labrador.app_finished';
    const EXCEPTION_THROWN = 'labrador.exception_thrown';

} 
