<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

use Evenement\EventEmitterInterface;

interface EventAwarePlugin extends Plugin {

    public function registerEventListeners(EventEmitterInterface $emitter);

} 
