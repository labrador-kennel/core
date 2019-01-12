# Labrador Plugins

A Labrador Plugin is the primary way for your code to interact with Labrador. It provides a 
way for you to group code and share it with not only your application but others. We typically 
expect a Plugin to do one or more of the following things:

- Register services to the Auryn container
- Add listeners to AsyncEvent\Emitter so that your code can respond to emitted events
- Perform some bootstrap function when Labrador first starts up
- Depend on some other Plugin to be loaded so that you have access to their services

Each one of these responsibilities is defined by its own interface; you implement an 
object that defines one or more of these interfaces and then register that Plugin with 
the Engine running your Application.

### ServiceAwarePlugin

```php
<?php

namespace Cspray\Labrador\Plugin;

use Auryn\Injector;

interface ServiceAwarePlugin {
    
    public function registerServices(Injector $injector);
}

?>
```

As the interface name and method implies this is how you register services to the 
Auryn container. You should refrain from making objects with this Plugin and instead 
wire up your object graph so appropriate services are shared or non-instantiable 
arguments are defined for future object construction.

### EventAwarePlugin

```php
<?php

namespace Cspray\Labrador\Plugin;

use Cspray\Labrador\AsyncEvent\Emitter;

interface EventAwarePlugin {
    
    public function registerEventListeners(Emitter $emitter);
}

?>
```

If you need to respond to one of Labrador's emitted events or an event emitted by your 
Application you should register any listeners with this type of Plugin. It is highly 
recommended that you do not emit events in this Plugin as there is no guarantee 
appropriate listeners will be registered.

### BootablePlugin

```php
<?php

namespace Cspray\Labrador\Plugin;

interface BootablePlugin {
    
    public function boot();
    
}

?>
```

Does your Plugin need to do something one-time when the app is booting up and before 
any of the Application code has been executed? Implementing this Plugin and performing 
your action in the `boot()` method is how you'd do that.

### PluginDependentPlugin

```php
<?php

namespace Cspray\Labrador\Plugin;

interface PluginDependentPlugin {
    
    public function dependsOn() : iterable;
    
}
?>
```

Perhaps your Plugin depends on another Plugin. For example, you might have a `YourApp\PdoPlugin` 
that properly instantiates, configures, and shares with the Auryn container a PDO object. To 
ensure that this Plugin is registered simply return it's fully qualified class name as an 
element in an array or Traversable. If the Plugin is registered with the Engine running 
your application it will be loaded (if it hasn't already been loaded) otherwise an 
Exception will be thrown indicating that your Application should register the Plugin.

## Pluggable

If your object can have Plugins attahed to it it should implement the Pluggable interface.

```php
<?php

namespace Cspray\Labrador\Plugin;

interface Pluggable {
    
    /**
     * @param Plugin $plugin
     * @return $this
     */
    public function registerPlugin(Plugin $plugin);

    /**
     * @param string $name
     */
    public function removePlugin(string $name);

    /**
     * @param string $name
     * @return boolean
     */
    public function hasPlugin(string $name) : bool;

    /**
     * @param string $name
     * @return Plugin
     */
    public function getPlugin(string $name) : Plugin;

    /**
     * An array of Plugin objects associated to the given Pluggable.
     *
     * @return Plugin[]
     */
    public function getPlugins() : array;

}

?>
```

Typically your code shouldn't need to implement this interface; the Engine interface is 
a Pluggable and it is highly recommended that you register Plugins on the Engine 
instance running your Application instead of on one of your own objects. If you do 
implement your own Pluggable then you should be sure to follow the Plugin loading 
process detailed below.

## Plugin Loading Process

During the `Engine::ENGINE_BOOTUP_EVENT` each Plugin registered with the Engine will go 
through the below process. This process happens synchronously; meaning that each Plugin 
is loaded in its entirety before the next Plugin is started.

While each responsibility of a Plugin is handled by its own interface it is certainly 
possible to implement all 4. In this case it might be important to know in what order 
each method will be called. Plugins are loaded in the following order:

1. Call `Plugin::dependsOn()` and load any dependent Plugins; meaning all of the plugins
   returned from this iterable will go through this process before the calling Plugin does.
2. Call `Plugin::registerServices(Injector)` and wire up any object graph that your Plugin 
   requires.
3. Call `Plugin::registerEventListeners(Emitter)` and register any listeners for 
   emitted events.
4. Call `Plugin::boot()` and allow your Plugin to go through any necessary bootstrapping.
 

You should take a look at the [Application](../application) documentation next to learn more 
about actually executing your application code.