<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador;

use Cspray\Labrador\Event\ExceptionThrownEvent;
use Auryn\Injector;

function bootstrap(callable $exceptionHandler = null ,callable $errorHandler = null) : Injector {
    set_error_handler($errorHandler ?: new ErrorToExceptionHandler());

    $excHandler = $exceptionHandler ?: new UncaughtExceptionHandler();
    set_exception_handler($excHandler);

    $injector = (new Services())->createInjector();

    $engine = $injector->make(Engine::class);
    $engine->onExceptionThrown(function(ExceptionThrownEvent $event) use($excHandler) {
        $excHandler($event->getException());
    });

    return $injector;
}