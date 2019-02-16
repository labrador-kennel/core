--TEST--
Ensures basic integration works
--FILE--
<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

$injector = (new \Cspray\Labrador\DependencyGraph())->wireObjectGraph();
$engine = $injector->make(\Cspray\Labrador\Engine::class);

$engine->onEngineBootup(function() {
    echo "init\n";
})
->onAppCleanup(function() {
    echo "cleanup";
});

$app = new \Cspray\Labrador\Test\Stub\ExceptionHandlerApplication(
    function() {
        echo "oops in app\n";
        throw new RuntimeException('exception in app');
    },
    function(Throwable $event) {
        $exc = get_class($event);
        echo "handle {$exc}\n";
    }
);


$engine->run($app);
?>
--EXPECTF--
init
oops in app
handle RuntimeException
cleanup