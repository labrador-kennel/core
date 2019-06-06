---
layout: docs
---
## Plugin Documentation

A Labrador Plugin is the primary way for your code to interact with Labrador. It provides a way for you to group
code and share it with not only your application but others. We typically expect a Plugin to do one or more of
the following things:

<ul class="content list-inside list-show-bullets">
  <li>Register an object graph with the Auryn container.</li>
  <li>Add listeners to <code>AsyncEvent\Emitter</code> so that your code can respond to emitted events.</li>
  <li>Perform some bootstrap function when the Engine first starts up.</li>
  <li>Depend on some other Plugin to be loaded so that you have access to APIs they provide.</li>
  <li>...whatever else you might need a Plugin to do! (see below for more details).</li>
</ul>

All Plugins, except potentially those you custom create, are defined by an interface. It is up to you to
implement any combination of those interfaces as necessary for your Plugin. All of the interfaces are explicitly
scoped to be as easy to implement as possible.

### InjectorAwarePlugin

As the name implies this Plugin lets you define its own discrete object graph that can be used by your Application 
and, possibly, shared with other apps as well. The typical example would be a Plugin that allows sharing a database
connection (or for more advanced applications an ORM EntityManager or similar). For our example, we're going to take 
a look at providing an object from the [amphp postgres](https://github.com/amphp/postgres) package.

```php
<?php 

use Cspray\Labrador\Plugin\InjectorAwarePlugin;
use Amp\Postgres\ConnectionConfig;
use Auryn\Injector;
use function Amp\Postgres\pool;

class PostgresPlugin implements InjectorAwarePlugin {
    
    private $connectionString;
    
    public function __construct(string $connectionString) {
        $this->connectionString = $connectionString;
    }
    
    public function wireObjectGraph(Injector $injector) : void {
        $config = ConnectionConfig::fromString($this->connectionString);
        $injector->share(pool($config));
    }
    
}

?>
```

Generally speaking Plugins should be discrete enough in functionality to not require a huge object graph but depending 
on what you need to wire up this can be as simple or as complex as necessary for your functionality.

<div class="message is-info">
  It is important to remember that your Plugin must be able to be instantiated by your Application's Injector. During your 
  bootstrapping process you should ensure that you have defined the appropriate scalar constructor value for this object.
</div>

### PluginDependentPlugin

### EventAwarePlugin

Sometimes Plugins need to respond to events that get triggered by your Application or Labrador itself. 

### BootablePlugin


### YourCustomPlugin

### Pluggable

#### Plugin Loading Process

During the `Engine::ENGINE_BOOTUP_EVENT` each Plugin registered with the Engine will go through the
below process. This process happens synchronously; meaning that each Plugin is loaded in its entirety before the
next Plugin is started.

  While each responsibility of a Plugin is handled by its own interface it is certainly possible to implement all
  of them. In this case it might be important to know in what order each method will be called. Plugins are loaded in
  the following order:

<ol type="1" class="content list-inside">
  <li>
    Call <code>Plugin::dependsOn()</code> and load any dependent Plugins; meaning all of the plugins returned from
    this iterable will go through this process before the calling Plugin does.
  </li>
  <li>
    Call <code>Plugin::registerServices(Injector)</code> and wire up any object graph that your Plugin requires.
  </li>
  <li>
    Call <code>Plugin::registerEventListeners(Emitter)</code> and register any listeners for emitted events.
  </li>
  <li>
    Invoke any handlers that have been registered with <code>Pluggable::registerPluginHandler</code> that matches
    the specific type of Plugin being loaded.
  </li>
  <li>
    Call <code>Plugin::boot()</code> and allow your Plugin to go through any necessary bootstrapping.
  </li>
</ol>

<hr />

<a href="" class="is-pulled-left is-size-6">
  <span class="icon">
    <i class="fas fa-angle-left"></i>
  </span>
  Home
</a>

<a href="applications" class="is-pulled-right is-size-6">
  Applications
  <span class="icon">
    <i class="fas fa-angle-right"></i>
  </span>
</a>
