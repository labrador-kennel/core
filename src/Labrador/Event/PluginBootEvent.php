<?php

declare(strict_types=1);

/**
 * An event triggered once before the application is executed; this should give
 * every Plugin registered to the Engine the ability to carry out any procedures
 * it may need to execute.
 *
 * @license See LICENSE in source root
 */

namespace Labrador\Event;

use Labrador\Engine;

class PluginBootEvent extends Event {

    public function __construct() {
        parent::__construct(Engine::PLUGIN_BOOT_EVENT);
    }

}
