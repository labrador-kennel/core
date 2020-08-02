<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

class BadApplication extends \Cspray\Labrador\AbstractApplication {

    public function handleException(Throwable $throwable) : void {
        throw new RuntimeException('Thrown from handleException. Previous message: ' . $throwable->getMessage());
    }

    protected function doStart() : \Amp\Promise {
        throw new RuntimeException('Thrown from doStart.');
    }
}

$logger = new \Monolog\Logger('labrador.exception-test');
$logger->pushHandler(new \Amp\Log\StreamHandler(\Amp\ByteStream\getStdout()));

$injector = (new \Cspray\Labrador\DependencyGraph($logger))->wireObjectGraph();
$app = $injector->make(BadApplication::class);
$engine = $injector->make(\Cspray\Labrador\Engine::class);

$engine->run($app);