--TEST--
Ensures basic integration works
--FILE--
<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

$configuration = new class implements \Cspray\Labrador\Configuration {

        public function getLogName() : string {
            return 'integration-test';
        }

        public function getLogPath() : string {
            return '/dev/null';
        }

        public function getInjectorProviderPath() : string {
            throw new \RuntimeException('Did not expect this to be called');
        }

        public function getPlugins() : \Ds\Set {
            throw new \RuntimeException('Did not expect this to be called');
        }
};

$injector = (new \Cspray\Labrador\DependencyGraph($configuration))->wireObjectGraph();
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

$app = $injector->make(\Cspray\Labrador\CallbackApplication::class, [
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