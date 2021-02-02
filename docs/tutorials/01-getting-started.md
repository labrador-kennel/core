# Getting Started

While getting started with Labrador should be simple out-of-the-box there is some bootstrapping necessary to get your 
code running. Specifically you'll need to implement an ApplicationObjectGraph and an Application. An 
ApplicationObjectGraph represents the code necessary to define your dependencies on an Auryn Injector. The Application 
is, well, your app! If you're unfamiliar with Auryn we recommend you [read over the documentation](https://github.com/rdlowrey/auryn).

## Directory Structure

Though Labrador makes strong opinions about several aspects of building an Application it does not make opinions on how 
you should physically structure the code itself. 

## Feature complete bootstrapping

```php
<?php

// app.php in your project's root directory

require_once __DIR__ . '/vendor/autoload.php';

use Cspray\Labrador\Application;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\CoreApplicationObjectGraph;
use Cspray\Labrador\Engine;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use Cspray\Labrador\SettingsLoader\SettingsLoaderFactory;
use Amp\Promise;
use Amp\Delayed;
use Amp\Log\StreamHandler;
use Auryn\Injector;
use Monolog\Logger;
use function Amp\call;
use function Amp\ByteStream\getStdout;

class HelloWorldApplicationObjectGraph extends CoreApplicationObjectGraph {

    public function wireObjectGraph() : Injector{
        $injector = parent::wireObjectGraph();

        // Make sure that any place that type hints for an Application gets our implementation
        $injector->alias(Application::class, HelloWorldApplication::class);

        // Wire the rest of your dependencies

        return $injector;
    }

}

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

// The EnvironmentType you set may determine Settings loaded or other pieces of your app based on the 
// Plugins and bootstrap you use
$appEnvironment = EnvironmentType::valueOf(getenv('LABRADOR_APP_ENV') ?? 'development'); 
$environment = new StandardEnvironment($appEnvironment);

// Be sure to customize the logger to be appropriate for your needs!
$logger = new Logger('labrador.hello-world', [new StreamHandler(getStdout())]);

$settingsLoader = SettingsLoaderFactory::defaultFileSystemSettingsLoader(__DIR__ . '/config', $appEnvironment);

$injector = (new HelloWorldApplicationObjectGraph($environment, $logger))->wireObjectGraph();

// We want to make sure that the dependencies required by AbstractApplication are autowired using the $injector
$app = $injector->make(Application::class);

// Note we are making an _interface_ and not an implementation. By default this will return an AmpEngine instance
$engine = $injector->make(Engine::class);

$engine->run($app);
```

## Important Concepts

Based on the simple boilerplate above there's some important concepts to take into consideration when it comes to 
creating Labrador applications that we believe should be important in your own applications.

### Logging is mission-critical

You'll notice that our example doesn't just `echo` out `"Hello Labrador!"` but goes so far as to log information using a 
Logger. If you were to actually run this example you'd see even more information logged. If you registered Plugins you'd 
see even _more_ detailed information logged about exactly what is happening during the Plugin loading process. Real life 
experience has proved that proper logging can make an application easier to maintain.

You should treat logging the same in your own application. If something meaningful occurs in your app you should log it. 
Gaining access to the Logger is simple. Implement the `Psr\Log\LoggerAwareInterface`; extending this interface is how 
`Application` implementations gain access to the Logger. Next, use the `Psr\Log\LoggerAwareTrait`; the `AbstractApplication` 
implementation uses this. As long as your bootstrapping code makes proper use of the `CoreApplicationObjectGraph` then 
this will be taken care of for you.

### Dependency Injection is important

The `$injector` in the code above plays a highly critical role in the running of a Labrador application. We make use of 
Dependency Injection with simple interfaces at code boundaries to make code easy to test and easy to replace. The [Auryn](https://github.com/rdlowrey/auryn)
injector helps make the pain of Dependency Injection go away. Some cool things the `$injector` is doing for us includes 
taking care of the dependency that is required by the `AbstractApplication` interface and allowing us to create our `Engine` 
instance using the `Engine` interface. Different libraries can define their own implementation to use for the `Engine`. The 
`$injector` is also how we take care of ensuring that `Psr\Log\LoggerAwareInterface` instances have the Logger set on 
object creation. 

If you have not done so yet check out Labrador's `CoreApplicationObjectGraph` source code. It gives a good baseline for the 
functionality provided by Auryn. After that you should [definitely check out the Auryn documentation](https://github.com/rdlowrey/auryn) 
and learn to embrace Inversion of Control.

## Next Steps

The real power of Labrador comes with its concept of "plugins". It is highly recommended you check out the 
[Plugins: Overview](/docs/core/tutorials/plugins-overview) next.
