# Getting Started

Getting started with Labrador should be simple out-of-the-box. There is some bootstrapping necessary to get your 
code running. Specifically you'll need to implement an `ApplicationObjectGraph` and an `Application`. An 
`ApplicationObjectGraph` represents the code necessary to define your dependencies on an [Auryn](https://github.com/rdlowrey/auryn) 
`Injector` (a 3rd party container). The `Application` is, well, your app!

## Feature complete bootstrapping

Providing the appropriate bootstrap that will get the Labrador `Engine` running is all that will need to be provided to 
get up and running. In our example bootstrap we're going to be creating a `HelloWorldApp` for the `Acme` company. The 
`HelloWorldApp` will include a dependency, so we can show how to properly interact with the `Injector`. All of the code 
below assumes that the appropriate autoloading is being handled by a PSR-4 style autoloader.

```php
<?php

// /src/WorldFactory.php

namespace Acme;

use Amp\Promise;
use function Amp\call;

class WorldFactory {

    /**
     * Although our WorldFactory is simple we have to anticipate that creating a world might take a while and should be 
     * asynchronous
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


## Next Steps

The real power of Labrador comes with its concept of "plugins". It is highly recommended you check out the 
[Plugins: Overview](/docs/core/tutorials/plugins-overview) next.
