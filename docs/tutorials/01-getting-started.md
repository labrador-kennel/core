# Getting Started

Getting started with Labrador should be simple out-of-the-box. There is some bootstrapping necessary to get your 
code running. Specifically you'll need to implement an `ApplicationObjectGraph` and an `Application`. An 
`ApplicationObjectGraph` represents the code necessary to define your dependencies on an [Auryn](https://github.com/rdlowrey/auryn) 
`Injector` (a 3rd party container). The `Application` is, well, your app!

## Creating a Labrador powered app

We'll create an example app powered by Labrador and the first thing we'll do is create the `Application` and 
`ApplicationObjectGraph`. The simple and traditional "Hello World!" will be used as our example. We will create an 
`Application` instance that will log to info our expected text. We'll also create a dependency that will determine what 
celestial body we're saying hello to. This example is meant to show a few key things that are important aspects of all
Labrador apps:

1. Extending from the `AbstractApplication` is the recommended way of creating `Application` implementations.
2. Properly constructing your `Application` with the `Injector` will intrinsically provide you with a PSR-compliant `Logger`.
3. `Application` dependencies _should_ be defined in the constructor.
4. Introducing you to the concept of a `Pluggable`. We won't go into details but important to realize that an `Application`
_is_ a `Pluggable` and we delegate those responsibilities to a different implementation.
   
### The Application and its Dependency

First up is the dependency the `Application` needs to determine what we're saying hello to. Our dependency will have a 
single method that returns a `Promise` that will resolve with a string identifying the name of our world. We built the 
method with **forward compatibility** in mind; some day our world creation might be resource intensive and will be able 
to easily swap to asynchronous world generation. It also further reinforces the fact that Labrador is meant to power 
asynchronous apps.

```php
<?php

// /src/WorldFactory.php

namespace Acme;

use Amp\Promise;
use function Amp\call;

class WorldFactory {

    /**
     * @return Promise<string>
     */
    public function createWorld() : Promise {
        return call(function() {
            $worlds = ['World', 'Earth', 'Mars', 'Pluto'];
            return $worlds[array_rand($worlds)];
        });
    }
    
}
?>
```

Next, we need to create the `Application` that will make use of our `WorldFactory` and execute the primary business logic 
for our app. We're going to extend from `AbstractApplication` as it handles a lot of boilerplate that may be required 
otherwise. For now, if you extend the `AbstractApplication` know that you'll only be required to provide the actual 
business logic for your app as well as 1 dependency, a `Pluggable` instance. If you'd like to learn about the `AbstactApplication` 
in more detail you can check out the ["Reference: Understanding AbstractApplication"](/docs/core/references/understanding-abstract-application)
article.

You'll also notice that your app has access to a PSR-3 compliant `LoggerInterface` implementation in the `$logger` property. You 
might be wondering where this object comes from since we don't have a `LoggerInterface` defined in the constructor. This 
is handled by the `Application` extending the PSR-3 `LoggerAwareInterface` and using the `Injector` to instantiate our 
app object. You can take advantage of this within your own codebase by implementing the `LoggerAwareInterface` and ensuring 
your object graph is wired correctly. Then all of your services and other code that need to log information will have 
access to your configured `Logger`!

```php
<?php

// /src/HelloWorldApp.php

namespace Acme;

use Amp\Promise;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\Plugin\Pluggable;
use function Amp\call;

class HelloWorldApp extends AbstractApplication {

    private $worldFactory;
    
    public function __construct(Pluggable $pluggable, WorldFactory $worldFactory) {
        parent::__construct($pluggable);
        $this->worldFactory = $worldFactory;
    }

    protected function doStart() : Promise {
        return call(function() {
            $world = yield $this->worldFactory->createWorld();
            $this->logger->info(sprintf("Hello %s!\n", $world)); 
        });
    }

}
```

Next, we'll create an `ApplicationObjectGraph` that registers our dependency and ensures that anyplace we're expecting 
an `Application` instance we get our `HelloWorldApp` implementation. This covers only the absolute basics of using Auryn's 
`Injector`. It is highly recommended that you review [the Auryn documentation](https://github.com/rdlowrey/auryn) if you 
aren't familiar with the library.

```php
<?php

namespace Acme;

use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\ApplicationObjectGraph;
use Cspray\Labrador\CoreApplicationObjectGraph;

class HelloWorldObjectGraph extends CoreApplicationObjectGraph implements ApplicationObjectGraph {

    public function wireObjectGraph() : Injector {
        /** @var Injector $injector */
        $injector = parent::wireObjectGraph();
        
        // Make sure that our objects are only created 1 time
        $injector->share(WorldFactory::class);
        $injector->share(HelloWorldApp::class);
        
        // Make sure that anywhere we call for an Application we get our app.
        $injector->alias(Application::class, HelloWorldApp::class);
    }

}
```

Finally, we wire everything together in the app's bootstrapping. For the most part everything that you see below is just 
putting together everything that we created above. The one piece that should be pointed out are the two environment objects;
an `EnvironmentType` enum and an `Environment` implementation. These allow you to easily know whether you're operating in 
production or development (or staging or testing) and to retrieve environment variables. 

The `Environment` is discussed more in the ["Tutorial: Configuring your app with Settings"](/docs/core/tutorials/settings-configuring-your-app) 
article. Outside a use case with loading configuration the `Environment` instance is not used within internal Labrador 
code. These objects are intended primarily to act as standardized, typed methods for inspecting the environment your 
app is running within your own code.

```php
<?php

namespace Acme;

// app.php in your project's root directory

require_once __DIR__ . '/vendor/autoload.php';

use Cspray\Labrador\Application;
use Cspray\Labrador\Engine;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use Cspray\Labrador\SettingsLoader\SettingsLoaderFactory;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use function Amp\ByteStream\getStdout;

// The EnvironmentType you set may determine Settings loaded or other pieces of your app based on the 
// Plugins and bootstrap you use. This should be one of the values: 'production', 'staging', 'development', 'test'
$appEnvironment = EnvironmentType::valueOf(getenv('LABRADOR_APP_ENV') ?? 'development'); 
$environment = new StandardEnvironment($appEnvironment);

// Be sure to customize the logger to be appropriate for your needs!
$logger = new Logger('labrador.hello-world', [new StreamHandler(getStdout())]);

$injector = (new HelloWorldObjectGraph($environment, $logger))->wireObjectGraph();

// We want to make sure that the dependencies required by AbstractApplication are autowired using the $injector
$app = $injector->make(Application::class);

// Note we are making an _interface_ and not an implementation. By default this will return an AmpEngine instance
$engine = $injector->make(Engine::class);

$engine->run($app);
```

If you execute this code multiple times you should see an info line in your terminal that will say "Hello World!" or 
maybe "Hello Mars!". At this point you have everything you need to start writing a more useful asynchronous application 
with [amphp](https://amphp.org)!

## Next Steps

Next you'll probably want to have some way of providing a configuration for your Application. For that you should check 
out the ["Tutorial: Configuring your app with Settings"](/docs/core/tutorials/settings-configuring-your-app) article. If 
you're interested in creating reusable code that can be shared across many different applications you should check out
["Tutorials: Sharing functionality with Plugins"](/docs/core/tutorials/plugins-sharing-functionality) article.
