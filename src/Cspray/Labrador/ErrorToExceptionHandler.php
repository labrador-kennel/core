<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador;

class ErrorToExceptionHandler {

    public function __invoke(int $severity, string $msg, string $file, int $lineNum) {
        throw new \ErrorException($msg, 0, $severity, $file, $lineNum);
    }

}