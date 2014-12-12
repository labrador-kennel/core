<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface EventAwarePlugin extends Plugin {

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher);

} 
