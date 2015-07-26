--TEST--
Ensures basic integration works
--FILE--
<?php

require_once dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

use Labrador\Services;
use Labrador\CoreEngine;

$injector = (new Services())->createInjector();
/** @var CoreEngine $engine */
$engine = $injector->make(CoreEngine::class);

$engine->onAppExecute(function() {
    echo 'Hello World';
});

$engine->run();
--EXPECTF--
Hello World