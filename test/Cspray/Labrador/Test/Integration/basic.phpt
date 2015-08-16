--TEST--
Ensures basic integration works
--FILE--
<?php

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/vendor/autoload.php';

use Cspray\Labrador\Event\ExceptionThrownEvent;
use function Cspray\Labrador\engine;

engine()
->onEnvironmentInitialize(function() {
    echo "init\n";
})
->onAppExecute(function() {
    echo "execute\n";
})
->onAppExecute(function() {
    echo "oops\n";
    throw new Exception;
})
->onExceptionThrown(function(ExceptionThrownEvent $event) {
    $exc = get_class($event->getException());
    echo "handle {$exc}\n";
})
->onAppCleanup(function() {
    echo "cleanup";
})
->run();
--EXPECTF--
init
execute
oops
handle Exception
cleanup