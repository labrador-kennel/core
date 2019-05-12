<?php declare(strict_types=1);


namespace Cspray\Labrador;

use Throwable;

abstract class StandardApplication implements Application {

    public function exceptionHandler(Throwable $throwable) : void {
        throw $throwable;
    }
}
