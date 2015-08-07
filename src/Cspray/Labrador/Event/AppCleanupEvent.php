<?php

declare(strict_types=1);

/**
 * An event triggered after the application has been executed; this is used to
 * ensure that if a Plugin needs to carry out any cleanup code it has the
 * ability to do so.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Event;

use Cspray\Labrador\Engine;

class AppCleanupEvent extends Event {

    public function __construct() {
        parent::__construct(Engine::APP_CLEANUP_EVENT);
    }

}
