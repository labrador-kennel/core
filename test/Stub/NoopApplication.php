<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\AbstractApplication;

class NoopApplication extends AbstractApplication {

    protected function doStart() : Promise {
        return new Success();
    }
}
