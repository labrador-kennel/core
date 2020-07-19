# Labrador core

[![GitHub release](https://img.shields.io/github/release/labrador-kennel/core.svg?style=flat-square)](https://github.com/cspray/labrador/releases/latest)
[![GitHub license](https://img.shields.io/github/license/labrador-kennel/core.svg?style=flat-square)](http://opensource.org/licenses/MIT)

Provides the core concepts for building applications on top of Labrador. Provides the default third-party dependencies 
that are required as well as important foundational concepts.

- **IoC Container** A recursive, autowiring Inversion of Control container provided through [Auryn].
- **Event** Trigger semantic, data-rich events taking full advantage of Amp's async nature with [async-event].
- **Plugin** A series of simple to implement interfaces that allow you to easily hook into Labrador execution and provide reusable modules!
- **Application** An interface that you implement, or extend from `AbstractApplication`, to encapsulate your app's business logic.
- **Engine** An interface that is responsible for running your Application on the Loop, logging, and tying everything together.

## Installation

[Composer] is the only supported method for installing Labrador packages.

```
composer require cspray/labrador
```

## Quick Start

If you'd rather get started quickly without having to read a bunch of documentation the code below demonstrates how to 
quickly get an `Application` implemented and running. Otherwise, we recommend checking out the Documentation section 
below for more information.

```php
<?php

// app.php in your project's root directory

require_once __DIR__ . '/vendor/autoload.php';

use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\DependencyGraph;
use Cspray\Labrador\Engine;
use Amp\Promise;
use Amp\Delayed;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use function Amp\call;
use function Amp\ByteStream\getStdout;

class HelloWorldApplication extends AbstractApplication {

    protected function doStart() : Promise {
        return call(function() {
            yield new Delayed(1);  // just to show that we are running on the Loop
            $this->logger->info('Hello Labrador!');
        }); 
    }

}

$logger = new Logger('labrador.hello-world');
$logger->pushHandler(new StreamHandler(getStdout()));

$injector = (new DependencyGraph($logger))->wireObjectGraph();

$app = $injector->make(HelloWorldApplication::class);
$engine = $injector->make(Engine::class);

$engine->run($app);
?>
```

## Documentation

Labrador packages have thorough documentation in-repo in the `docs/` directory. You can also check out the 
documentation online at [https://labrador-kennel.io/docs/core](https://labrador-kennel.io/docs/core).

## Governance

All Labrador packages adhere to the rules laid out in the [Labrador Governance repo](https://github.com/labrador-kennel/governance)

[Auryn]: https://github.com/rdlowrey/Auryn
[async-event]: https://github.com/labrador-kennel/async-event