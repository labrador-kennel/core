<?php

require_once __DIR__ . '/vendor/autoload.php';

use Cspray\Labrador\Engine;
use function Cspray\Labrador\bootstrap;

$injector = bootstrap();

$engine = $injector->make(Engine::class);

$application = new \Cspray\Labrador\HelloWorldApplication();

$engine->run($application);