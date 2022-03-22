<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Amp\Future;
use Cspray\Labrador\AbstractApplication;

class NoopApplication extends AbstractApplication {

    protected function doStart() : Future {
        return Future::complete();
    }
}
