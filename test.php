<?php

// app.php in your project's root directory

require_once __DIR__ . '/vendor/autoload.php';

use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\CoreApplicationObjectGraph;
use Cspray\Labrador\Engine;
use Cspray\Labrador\StandardEnvironment;
use Amp\Promise;
use Amp\Delayed;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use function Amp\call;
use function Amp\ByteStream\getStdout;

class HelloWorldApplicationObjectGraph extends CoreApplicationObjectGraph {

    public function wireObjectGraph() : \Auryn\Injector{
        $injector = parent::wireObjectGraph();

        // wire up your app's dependencies

        return $injector;
    }

}

class HelloWorldApplication extends AbstractApplication {

    protected function doStart() : Promise {
        return call(function() {
            yield new Delayed(500);  // just to show that we are running on the Loop
            $this->logger->info('Hello Labrador!');
        });
    }

}

$environment = new StandardEnvironment(EnvironmentType::Development());
$logger = new Logger('labrador.hello-world', [new StreamHandler(getStdout())]);

$injector = (new HelloWorldApplicationObjectGraph($environment, $logger))->wireObjectGraph();

$app = $injector->make(HelloWorldApplication::class);
$engine = $injector->make(Engine::class);

$engine->run($app);