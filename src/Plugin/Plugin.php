<?php

declare(strict_types=1);

/**
 * Objects that interact with the Labrador engine or an application written on top
 * of Labrador; primarily this will involve making use of the EventAwarePlugin and
 * ServiceAwarePlugin interfaces to respond to triggered events or provide services.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Plugin;

interface Plugin {

    /**
     * Perform any actions that should be completed by your Plugin before the
     * primary execution of your app is kicked off.
     */
    public function boot();

}
