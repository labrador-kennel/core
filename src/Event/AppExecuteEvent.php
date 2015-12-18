<?php

declare(strict_types=1);

/**
 * An event triggered once when your application should execute its
 * processing logic and deliver a result to the user.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Event;

use Cspray\Labrador\Engine;
use League\Event\Event;

class AppExecuteEvent extends Event {

    public function __construct() {
        parent::__construct(Engine::APP_EXECUTE_EVENT);
    }

}
