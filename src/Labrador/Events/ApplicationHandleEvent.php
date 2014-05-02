<?php

/**
 * An event triggered at the beginning of a Labrador\Application handling a
 * HttpKernelInterface::MASTER_REQUEST.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Events;

/**
 * Please note that you should only rely on this event on triggering exactly
 * ONE time per each request. This event will not trigger for
 * HttpKernelInterface::SUB_REQUEST and only HttpKernelInterface::MASTER_REQUEST.
 */
class ApplicationHandleEvent extends LabradorEvent {}
