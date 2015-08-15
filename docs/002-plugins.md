# Plugins

Once you get beyond simple, contrived examples you'll find that Plugins are an integral part 
of any Labrador powered application. With them you can register event listeners, provide 
services, and perform actions on startup. You do these things by implementing simple interfaces.

## `Cspray\Labrador\Plugin\Plugin`

This is the base Plugin and only requires one method to be implemented: `boot()`. As it suggests 
with this method your plugin should do anything it needs to do at startup. There's not much else 
to go over other than all other Plugin types extend this interface.

## `Cspray\Labrador\Plugin\EventAwarePlugin`

Implement this type of Plugin if you want to listen to events triggered by Labrador. It requires 
you implement the following method: `registerEventListeners(Evenement\EventEmitterInterface)`. 
As it suggests, you should register any events you want to trigger on the EventEmitter passed.

## `Cspray\Labrador\Plugin\ServiceAwarePlugin`

If you need to register services to the `Auryn\Injector` this is the Plugin you're looking for. 
It requires that you implement `registerServices(Auryn\Injector)`. Again, as the name suggests
you should add any services your plugin might provide to the container passed.

## `Cspray\Labrador\Plugin\PluginDependentPlugin`

As you start to work with `ServiceAwarePlugin` types you may find that you depend on other 
Plugins to be registered. With the `PluginDependentPlugin` you implement the following method: 
`dependsOn() : array`. You should return an array of Plugin class names that should be loaded 
before the implementing Plugin.

This is the least obvious Plugin and is best explained with an example:

```
<?php

use Cspray\Labrador\Plugin\ServiceAwarePlugin;
use Cspray\Labrador\Plugin\PluginDependentPlugin;

class PdoPlugin implements ServiceAwarePlugin {

    public function boot() {
    
    }

    public function registerServices(Auryn\Injector $injector) {
        $dsn = myVendorSpecificDsn();
        $pdo = new PDO($dsn);
        $injector->share($pdo);
    }

}

class ModelServicePlugin implements ServiceAwarePlugin, PluginDependentPlugin {

    public function boot() {
    
    }
    
    public function registerServices(Auryn\Injector $injector) {
        // MyModel has a constructor dependency for PDO
        $injector->share(MyModel::class);
    }
    
    public function dependsOn() : array {
        return [PdoPlugin::class];
    }

}

$engine = new Cspray\Labrador\CoreEngine();

$engine->registerPlugin(new ModelServicePlugin());
$engine->registerPlugin(new PdoPlugin());
```

The `ModelServicePlugin` would fail if created with the container and no PDO 
service has been registered. Fortunately our handy `PdoPlugin` does exactly that. 
By declaring that we depend on the plugin it will be loaded before our dependent 
plugin and any services it declares will be available to the container.

## `Cspray\Labrador\Plugin\Pluggable`

A Plugin, by its very definition, requires something to plug *into*. As you can 
probably guess that responsibility is handled by implementations of the Pluggable 
interface. With this interface you can do everything you need to manage Plugins 
for a given context.

Speaking of managing Plugins...

### `Cspray\Labrador\Plugin\PluginManager`

Part of the underlying contract with the Pluggable interface is that each one 
provides the functionality detailed for each Plugin type. The PluginManager 
is a concrete Pluggable implementation that does handle all the different Plugin 
types. It is recommended that if you implement your own Engine you proxy all 
of the Pluggable methods to a PluginManager.