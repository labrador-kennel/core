---
title: Quick Start 
order: 1
---
Creating your Labrador application should be simple and straightforward; implement the `Application` interface and write 
appropriate bootstrapping code for your use case. In the guide below we'll discuss how to get started quickly, so you 
can focus on your application code. If you want more information on how to structure your code using Labrador 
conventions please checkout [Deep Dive: Application][deep-dive-app].

We're going to create a simple "Hello Labrador" application. We're going to put the implementation and bootstrapping in 
the same file, in a more complete solution you'd want to separate these appropriately.

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

// Adjust the logging handler used to match your needs
$stdoutHandler = new StreamHandler(getStdout());
$logger->pushHandler($stdoutHandler);

// If you have an existing Auryn\Injector you can pass it to wireObjectGraph to have Labrador's objects registered into 
// the passed container. The injected $logger will be set on all LoggerAwareInterface implementations that are created 
// by the $injector.
$injector = (new DependencyGraph($logger))->wireObjectGraph();

// We want to use the $injector to make these objects to ensure appropriate dependencies are autowired
$app = $injector->make(HelloWorldApplication::class);
$engine = $injector->make(Engine::class);

$engine->run($app);
```

That's it! If you ran this code you'd see some logging output in your terminal. Amongst that output would be our 
"Hello Labrador!" line. Labrador strives to provide extensive logging information and the `Application` interface also 
extends `Psr\Log\LoggerAwareInterface` to ensure you have access to your app's logger.

<div class="message is-info">
    <div class="message-body">
        We encourage appropriate logging for all of your Labrador code. When using the <code>Injector</code> provided by
        the <code>DependencyGraph</code> all you have to do is implement the <code>Psr\Log\LoggerAwareInterface</code> 
        and you'll have access to your logger on object creation.
    </div>
</div>

### Next Steps

The real power of Labrador comes with its concept of "plugins". It is highly recommended you check out the 
[Plugins: Overview][plugins-overview] next.

[deep-dive-app]: {{site.baseurl}}/references/application-deep-dive