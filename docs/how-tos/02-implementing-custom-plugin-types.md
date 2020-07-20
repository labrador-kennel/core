# Implementing custom Plugin types

Though we believe that the built-in Plugin types will satisfy the majority of use cases it is possible that you need to 
implement plugin-like functionality that isn't covered by any of the existing interfaces. In that case you should implement 
your own Plugin interface and adjust your Application to properly load it.

In our example we're going to create a `ConfigurationPlugin` that allows different configuration values to be set during 
the Plugin loading process and unset when the Plugin is removed. In the example below we'll assume that the `Acme\Configuration` 
type is already implemented.

### Step 1 - Implement your new interface

```php
<?php

namespace Acme;

use Amp\Promise;
use Cspray\Labrador\Plugin\Plugin;

interface ConfigurationPlugin extends Plugin {

    public function setConfiguration(Configuration $configuration) : Promise;

    public function unsetConfiguration(Configuration $configuration) : void;

}
```

### Step 2 - Adjust the Application to load your Plugin

During the Plugin loading process, which we cover in more detail in [Deep Dive: Plugins][plugins-deep-dive], you can 
run your own custom code for specific Plugin types. Just add the appropriate closuers to the `Application` in your 
bootstrapping code.

```php
<?php

namespace Acme;

use Cspray\Labrador\DependencyGraph;
use Cspray\Labrador\Engine;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use function Amp\ByteStream\getStdout;

$logger = new Logger('labrador.code-example');
$logger->pushHandler(new StreamHandler(getStdout()));

$injector = (new DependencyGraph($logger))->wireObjectGraph();

$app = $injector->make(MyApplication::class);
$config = $injector->make(Configuration::class);

$app->registerPluginLoadHandler(ConfigurationPlugin::class, function(ConfigurationPlugin $configurationPlugin) use($config) {
    $configurationPlugin->setConfiguration($config);
});

$app->registerPluginRemoveHandler(ConfigurationPlugin::class, function(ConfigurationPlugin $configurationPlugin) use($config) {
    $configurationPlugin->unsetConfiguration($config);
});

$engine = $injector->make(Engine::class);
$engine->run($app);
```

The important part to note here is that we're registering both a load and remove handler for any Plugin that implements 
the `ConfigurationPlugin`. During the Plugin loading process if the plugin implements `ConfigurationPlugin` it'll be 
passed into your callback. The return value of your loading handler should be a `Promise` or null. There should be 
no return value for remove handlers.

### Step 3 - Register implementations of your new plugin type

Now the only thing we need to implement the new type and register it with our Application.

```php
<?php

namespace Acme;

use Amp\Promise;
use function Amp\call;

class MyConfigurationPlugin implements ConfigurationPlugin {

    public function setConfiguration(Configuration $configuration) : Promise {
        return call(function() use($configuration) {
            // we could load something from a file or network call
            $configuration->set('foo', 'bar');
        });
    }

    public function unsetConfiguration(Configuration $configuration) : void {
        $configuration->unset('foo');
    }

}

// All the code in your bootstrap file to get your Application created
$app->registerPlugin(MyConfigurationPlugin::class);
```
