# Getting Started

Getting started with Labrador should be simple out-of-the-box. Extend the `AbstractApplication` class and implement the 
`AbstractApplication::doStart` method. This method should return a `Amp\Promise` that resolves when your app has finished 
running. If you've already read the Quick Start on the README some code below may look familiar to you. However, there 
are some important additions in this documentation compared to the README so review the code carefully.

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

    public function handleException(Throwable $throwable) : void{
        parent::handleException($throwable);  // the AbstractApplication will log the $throwable as an error 
        // If you need to do something special when an exception is thrown in your app other than logging it
    }

}

// Be sure to customize the logger to be appropriate for your needs!
$logger = new Logger('labrador.hello-world');
$logger->pushHandler(new StreamHandler(getStdout()));

// More information about how to create your own DependencyGraph can be found in /docs/how-tos/creating-your-dependency-graph
$injector = (new DependencyGraph($logger))->wireObjectGraph();

// We want to make sure that the dependencies required by AbstractApplication are autowired using the $injector
$app = $injector->make(HelloWorldApplication::class);

// Note we are making an _interface_ and not an implementation. By default this will return an AmpEngine instance
$engine = $injector->make(Engine::class);

$engine->run($app);
```

### Important Concepts

Based on the simple boilerplate above there's some important concepts to take into consideration when it comes to 
creating Labrador applications that we believe should be important in your own applications.

#### Logging is mission-critical

You'll notice that our example doesn't just `echo` out `"Hello Labrador!"` but goes so far as to log the information out. 
If you were to actually run this example you'd see even more information logged. If you registered Plugins you'd see 
even _more_ detailed information logged about exactly what is happening during the Plugin loading process. Real life 
experience has proved that proper logging can make an application easier to maintain.

You should treat logging the same in your own application. If there's anything meaningful that occurs in your app you 
should log it. Gaining access to the Logger is simple. Implement the `Psr\Log\LoggerAwareInterface`; the `Application` 
interface extends this. Next, use the `Psr\Log\LoggerAwareTrait`; the `AbstractApplication` implementation uses this. As 
long as the `$injector` from the `DependencyGraph` above create your object then that object will automatically have the 
`$logger` property set.

#### Dependency Injection is important

The `$injector` in the code above plays a highly critical role in the running of a Labrador application. We make use of 
Dependency Injection with simple interfaces at code boundaries to make code easy to test and easy to replace. The [Auryn]
injector helps make the pain of Dependency Injection go away. Some cool things the `$injector` is doing for us includes 
taking care of the dependency that is required by the `AbstractApplication` interface and allowing us to create our `Engine` 
instance using the `Engine` interface. Different libraries can define their own implementation to use for the `Engine`. The 
`$injector` is also how we take care of ensuring that `Psr\Log\LoggerAwareInterface` instances have the Logger set on 
object creation. 

If you have not done so yet check out Labrador's `DependencyGraph` source code. It gives a good baseline for the 
functionality provided by Auryn. After that you should [definitely check out the Auryn documentation][Auryn] and learn 
to embrace Inversion of Control.

### Next Steps

The real power of Labrador comes with its concept of "plugins". It is highly recommended you check out the 
[Plugins: Overview][plugins-overview] next.

[deep-dive-app]: /docs/core/references/application-deep-dive
[plugins-overview]: /docs/core/tutorials/plugins-overview
[Auryn]: https://github.com/rdlowrey/auryn