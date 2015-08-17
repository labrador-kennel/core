<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador;

use Exception;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class UncaughtExceptionHandler {

    public function __invoke(Exception $exception) {
        $whoops = new Run();
        $whoops->pushHandler(new PlainTextHandler);
        echo $whoops->handleException($exception);
    }

}