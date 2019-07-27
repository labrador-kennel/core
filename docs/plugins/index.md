---
layout: docs
---
## Plugin Documentation

A Labrador Plugin is the primary way for your code to interact with Labrador. It provides a way for you to group
code and share it with not only your application but others. We typically expect a Plugin to do one or more of
the following things:

<ul class="content list-inside list-show-bullets">
  <li>Register an object graph with the Auryn Injector.</li>
  <li>Add listeners to <code>AsyncEvent\Emitter</code> so that your code can respond to emitted events.</li>
  <li>Perform some bootstrap function when the Engine first starts up.</li>
  <li>Depend on some other Plugin to be loaded so that you have access to APIs they provide.</li>
  <li>...whatever else you might need a Plugin to do! (see below for more details).</li>
</ul>

All Plugins, except potentially those you custom create, are defined by an interface. It is up to you to
implement any combination of those interfaces as necessary for your Plugin. All of the interfaces are explicitly
scoped to be as easy to implement as possible.

We'll demonstrate how to use each of the provided interfaces by implementing a simple analytics tracking plugin that 
records when an Engine boots up and when specific events happen in your Application.

First, let's assume that we have an implementation of the following interface:

```php
<?php

interface AnalyticsTracker {
    
    public function record(string $eventName, array $eventData) : \Amp\Promise;
    
}

```

We'll call that implementation `AnalyticsTrackerImpl`. Let's create an InjectorAwarePlugin to register this implementation 
in our object graph.

```php
<?php

use Cspray\Labrador\Plugin\InjectorAwarePlugin;
use Auryn\Injector;

class AnalyticsTrackerInjectorPlugin implements InjectorAwarePlugin {
    public function wireObjectGraph(Injector $injector) : void {
        $injector->share(AnalyticsTracker::class);
        $injector->alias(AnalyticsTracker::class, AnalyticsTrackerImpl::class); 
    }
}
```

{% include message.html
   message_type="is-info"
   title="Auryn Injector"
   body="If you're unsure what the above code does you should take a look over the <a href=\"https://github.com/rdlowrey/auryn\">Auryn Injector documentation</a>."
%}

Next, we'll create a Plugin that implements several interfaces to provide the rest of the described functionality.

```php
<?php

use Cspray\Labrador\Engine;
use Cspray\Labrador\Plugin\PluginDependentPlugin;
use Cspray\Labrador\Plugin\EventAwarePlugin;
use Cspray\Labrador\Plugin\BootablePlugin;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\AsyncEvent\Event;
use Amp\Promise;
use function Amp\call;

class AnalyticsTrackerPlugin implements PluginDependentPlugin, EventAwarePlugin, BootablePlugin {
    
    private $analyticsTracker;
    private $listenerIds = [];
    
    public static function dependsOn() : array{
        return [
            AnalyticsTrackerInjectorPlugin::class    
        ];
    }
    
    public function __construct(AnalyticsTracker $analyticsTracker) {
        $this->analyticsTracker = $analyticsTracker; 
    }
    
    public function registerEventListeners(Emitter $emitter) : void {
        $this->listenerIds[] = $emitter->on("your-app-event-ns.order_completed", function(Event $event) {
            $order = $event->target();
            yield $this->analyticsTracker->record("order_completed", [
                'order_id' => $order->getId(),
                'user_id' => $order->getUserId() 
            ]);
        });
        
        $this->listenerIds[] = $emitter->on(Engine::ENGINE_SHUTDOWN_EVENT, function(Event $event) {
            yield $this->analyticsTracker->record("application_shutdown", [
                'time' => time() 
            ]);
        });
    }
    
    public function removeEventListeners(Emitter $emitter) : void {
        foreach ($this->listenerIds as $listenerId) {
            $emitter->off($listenerId);
        }
    }
    
    public function boot() : Promise {
        return call(function() {
            yield $this->anayticsTracker->record("application_startup", [
                'time' => time() 
            ]);
        });
    }
}
```

Finally, we attach this to a Pluggable implementation, `Pluggable::registerPlugin(AnalyticsTrackerPlugin::class)`, and 
call `Pluggable::loadPlugins()`. If you're attaching your Plugin to an Application and passing it to `Engine::run` then 
calling `loadPlugins` is taken care of for you.

The example above demonstrates how you can separate out your Plugin's dependencies from what your Plugin does in a way 
that leads to an easily testable Plugin and dependencies that could potentially be utilized by other Plugins or
Applications. Additionally, as your Plugin starts to grow in complexity you could refactor so that the boot and event 
tracking code are their own Plugins.

## YourCustomPlugin

Perhaps the above is not suitable for your use case and you need to implement your own custom Plugin that implements 
its own loading process. The `Pluggable::registerPluginLoadHandler` allows you to accomplish this by invoking a callback 
during the loading process for any Plugin that matches the type assigned to the handler. This handler is allowed to be 
asynchronous and can return a Promise or a Generator and it will resolve to completion.

If you are making use of the `Pluggable::removePlugin` method and need your Plugin to execute a corresponding unload 
procedure take a look at a `Pluggable::registerPluginRemoveHandler`.

## Plugin Loading Process

When the Plugin's Pluggable has its loadPlugins method invoked each Plugin registered with the Pluggable will go through 
the loading process. This process happens linearly; meaning that each Plugin is loaded in its entirety before the
next Plugin is started. Some aspects of this process can execute asynchronous code.

While each responsibility of a Plugin is handled by its own interface it is certainly possible to implement all
of them. Or perhaps you're implementing your own Pluggable (have you taken a look at PluginManager?). In either of 
these cases it might be important to know in what order each method will be called. Plugins SHOULD BE loaded in 
a specific order. This order is guaranteed when using provided Labrador implementations of Pluggable and should 
be implemented in your own Pluggable.

It is highly recommended that you review the [API Documentation for the Pluggable interface][pluggable-api-src] as it 
describes in detail the expected loading process.

Next up is the Engine, the piece that makes your Application run.

{% include next_previous_article_nav.md 
   previous_article_name="Applications"
   next_article_name="Engines"
%}

[pluggable-api-src]: https://github.com/labrador-kennel/core/blob/master/src/Plugin/Pluggable.php
