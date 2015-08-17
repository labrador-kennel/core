<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador;

use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

class UncaughtExceptionHandler {

    public function __invoke($exception) {
        var_dump($exception->getMessage());
        var_dump($exception->getFile());
        var_dump($exception->getLine());
        exit;
        $whoops = new Run();
        $whoops->pushHandler(new PlainTextHandler);
        echo $whoops->handleException($exception);
    }

}