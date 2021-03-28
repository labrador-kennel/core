# Labrador Core

[![PHP Unit Testing & Code Lint](https://github.com/labrador-kennel/core/workflows/PHP%20Unit%20Testing%20&%20Code%20Lint/badge.svg)](https://github.com/labrador-kennel/core/actions?query=workflow%3A%22PHP+Unit+Testing+%26+Code+Lint%22)
[![GitHub release](https://img.shields.io/github/release/labrador-kennel/core.svg?style=flat-square)](https://github.com/cspray/labrador/releases/latest)
[![GitHub license](https://img.shields.io/github/license/labrador-kennel/core.svg?style=flat-square)](http://opensource.org/licenses/MIT)

An opinionated, asynchronous micro-framework written on top of [amphp](https://amphp.org). Built using SOLID principles, 
unit testing, and a modular ecosystem Labrador aims to be a production-ready framework for creating asynchronous PHP
applications. Labrador Core serves as the foundation for this framework and provides important key concepts for building 
apps with Labrador.

## Installation

[Composer](https://getcomposer.org) is the only supported method for installing Labrador packages.

```
composer require cspray/labrador
```

## Quick Start

If you'd rather get started quickly without having to read a bunch of documentation the code below demonstrates how to 
quickly get an `Application` implemented and running. Otherwise, we recommend checking out the Documentation for more 
detailed information, and a complete guide to getting started.

```php
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
use Auryn\Injector;
use Monolog\Logger;
use function Amp\call;
use function Amp\ByteStream\getStdout;

class HelloWorldApplicationObjectGraph extends CoreApplicationObjectGraph {

    public function wireObjectGraph() : Injector {
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
```

## Documentation

Labrador packages have thorough documentation in-repo in the `docs/` directory. You can also check out the 
documentation online at [https://labrador-kennel.io/docs/core](https://labrador-kennel.io/docs/core).

## Governance

All Labrador packages adhere to the rules laid out in the [Labrador Governance repo](https://github.com/labrador-kennel/governance)
