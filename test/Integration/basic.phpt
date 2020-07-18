--TEST--
Ensures basic integration works
--FILE--
<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';


$injector = (new \Cspray\Labrador\DependencyGraph(new \Psr\Log\NullLogger()))->wireObjectGraph();
$engine = $injector->make(\Cspray\Labrador\Engine::class);

$engine->onEngineBootup(function() {
    echo "init\n";
})
->onEngineShutdown(function() {
    echo "shutdown";
});

set_error_handler(function(...$args) {
    var_dump($args);
});

$app = $injector->make(\Cspray\Labrador\Test\Stub\TestApplication::class, [
    'pluggable' => \Cspray\Labrador\Plugin\PluginManager::class,
    ':executeHandler' => function() {
        echo "oops in app\n";
        throw new RuntimeException('exception in app');
    },
    ':exceptionHandler' => function(Throwable $event) {
        $exc = get_class($event);
        echo "handle {$exc}\n";
    }

]);

$engine->run($app);
?>
--EXPECTF--
init
oops in app
handle RuntimeException
shutdown