---
title: "Plugins: Overview"
order: 2
---

Plugins are discrete, reusable pieces of functionality that can be easily utilized by any number of Labrador 
Applications. There are a variety of built-in Plugin interfaces that, when implemented, allow you to easily hook into 
the operations of a Labrador Application. In addition, it is possible to fully customize the Plugin loading process with 
your own Plugin types... meaning it would be easy for you to come up with your own Plugin and have it hook into Labrador.

In this guide we'll go over a high-level overview of the Plugin and the corresponding Pluggable. For more details about 
how to hook into specific Labrador functionality please check out the Plugin related tutorials.

### Plugin

The `Plugin` interface is incredibly simple to implement. It is just a marker interface that requires no methods to be 
implemented on it. Realistically you should not be implementing the `Plugin` interface directly but instead should be 
using one of the interfaces that extend `Plugin`. Additionally, it is important any custom Plugin types you create 
implements or extends the `Plugin` interface.

### Pluggable

A Plugin needs something to plug into. That's where the `Pluggable` interface comes along. The Pluggable is ultimately 
responsible for managing the entire lifecycle of a Plugin; from registering it to handling appropriate loading procedures.
The `Application` interface extends Pluggable, your app should be the only Pluggable you need to interact with for the 
vast majority of use cases. If you extend the `AbstractApplication` class all the responsibilities of the Pluggable will 
already be taken care of. In the normal use case you'll really only be responsible for registering the correct plugins 
for your application.

<div class="message is-warning">
    <div class="message-header">
        Implementing your own Pluggable
    </div>
    <div class="message-body">
        It is critical that if you implement your own Pluggable interface that you handle a variety of use cases and 
        potential problems. It is <strong>highly recommended</strong> that you use Labrador's <code>PluginManager</code>
        to delegate the responsibilities of the Pluggable. For more information please see the reference material, 
        <a href="{{site.baseurl}}/references/plugins-deep-dive">Deep Dive: Plugins &amp; Pluggables</a>.
    </div>
</div>

#### Registering your Plugin

You register Plugins by providing the Pluggable the fully-qualified class name. This should happen during your bootstrapping 
process and **MUST** happen before `Engine::run()` is called. Attempting to register plugins after the engine has started 
will result in an exception. Continuing with the bootstrap file created during our [Quick Start], if you had a plugin 
called `Acme\Foo\BarPlugin` we'd register it on your application like so.

```php
<?php

// ... all of the previous code from the quick start guide
// $logger = Psr\Log\LoggerInterface implementation

$injector = (new DependencyGraph($logger))->wireObjectGraph();

// We want to use the $injector to make these objects to ensure appropriate dependencies are autowired
$app = $injector->make(HelloWorldApplication::class);

$app->registerPlugin(BarPlugin::class);

$engine = $injector->make(Engine::class);

$engine->run($app);
```

Ultimately your application's `Auryn\Injector` will instantiate your Plugin. If you require a dependency that isn't 
provided by another Plugin you should ensure that the appropriate object graph has been wired for the `$injector`. If 
you're looking for more details on how to wire up your application's dependencies _that aren't provided by Plugins_ please
check out [Creating your DependencyGraph].

#### Plugin Loading

Plugins go through their loading process as one of the very first steps when you call `Engine::run`. Loading of Plugins 
happens asynchronously and your Plugins have opportunities to run asynchronous code before your Application starts. The 
complete loading process is too complicated to discuss in this guide. The most important thing to note is that Plugin 
loading starts immediately when you call `Engine::run` and all Plugins MUST be registered before this happens.

### Next Steps

{% include core/plugin_next_steps.md hide='overview' %}

[Quick Start]: {{site.baseurl}}/tutorials/quick-start
[Creating your DependencyGraph]: {{site.baseurl}}/how-tos/creating-your-dependency-graph
