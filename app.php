<?php

require_once __DIR__ . '/vendor/autoload.php';

use Cspray\Labrador\CoreEngine;
use function Cspray\Labrador\bootstrap;

$injector = bootstrap();

$engine = $injector->make(CoreEngine::class);

$engine->onAppExecute(function() {
    echo 'Hello World';
});

$engine->run();