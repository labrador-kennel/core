---
layout: docs
---
## Application Documentation

A Labrador Application from a high-level is an encapsulation of all the object graph, 
event listeners, and initialization code for your application. Lower level an Application 
is a Plugin that implements 3 additional methods that helps distinguish it from more generic 
Plugins.

### The Application Interface

```php
<?php

namespace Cspray\Labrador;

use Cspray\Labrador\Plugin\{
    BootablePlugin,
    EventAwarePlugin,
    PluginDependentPlugin,
    ServiceAwarePlugin, 
    Pluggable
};
use Amp\Promise;
use Throwable;

/**
 * Check out docs/plugins for more information about the various Plugins.
 */
interface Application extends BootablePlugin, EventAwarePlugin, PluginDependentPlugin, ServiceAwarePlugin {

    /**
     * This is where you execute all your business and application logic in the 
     * Amp event loop; resolve the Promise when your app is ready to shut down.  
     */
    public function execute() : Promise;
    
    /**
     * Handle when an exception is thrown by your app or is thrown by a registered 
     * event listener.
     * 
     * If your application cannot gracefully handle this exception rethrow it to 
     * cause Labrador to shut down.
     */
    public function exceptionHandler(Throwable $throwable) : void;
    
}
```

By implementing the available Plugin interfaces you can easily integrate your code into Labrador. 
The other methods are really the meat of your application; execute() is where you actually 
do your stuff and your Application::exceptionHandler will be used to handle exceptions thrown in the event loop.

If you extend your Application from `Cspray\Labrador\StandardApplication` the only method 
you'll need to implement is `execute()`; leaving the others to be implemented as you need them.
We do recommen that you also override the default `exceptionHandler` implementation as 
it will only rethrow the exception causing your application to fatally crash for any exception 
thrown.

<hr />

<a href="plugins" class="is-pulled-left is-size-6">
  <span class="icon">
    <i class="fas fa-angle-left"></i>
  </span>
  Plugins
</a>

<a href="engines" class="is-pulled-right is-size-6">
  Engines
  <span class="icon">
    <i class="fas fa-angle-right"></i>
  </span>
</a>
