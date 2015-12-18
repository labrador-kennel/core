<?php

declare(strict_types=1);

/**
 * Triggered after the application has been executed. Used to ensure that
 * any necessary cleanup code has the ability to execute.
 *
 * It is anticipated that this event will be triggered even if an exception
 * is thrown. It is possible that this event may not be triggered if the
 * application code calls `exit` before the event is triggered or if
 * a `labrador.exception-thrown` event listener throws an exception itself.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Event;

use Cspray\Labrador\Engine;
use League\Event\Event;

class AppCleanupEvent extends Event {

    public function __construct() {
        parent::__construct(Engine::APP_CLEANUP_EVENT);
    }

}
