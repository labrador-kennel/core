---
layout: docs
---
## Application Documentation

An Application is an implementation that represents the processing of your app's business logic. Application instances 
are also responsible for managing the Plugins that it requires to operate correctly. Although the Application interface 
itself is only 2 handler methods the Pluggable interface it extends has far more methods and more specific expectations 
about how it operates. To help ease this burden Labrador provides an AbstractApplication implementation out of the box 
that takes care of Plugin management for you. This guide shows you how to use that implementation.

{% include message.html
   message_type="is-info"
   title="Configuration"
   body="If you have not already done so you should check out the <a href=\"configuration\">Configuration documentation</a> for how to setup your Injector provider with your Application information."
%}

### Usage Guide

Below is a highly-contrived example of how to implement the Application interface using the 
ubiquitous "Hello World" example with some async thrown on top.

```php
<?php

class HelloWorldService {
    public function sayIt() : \Amp\Promise {
        return new \Amp\Success('Hello World!');
    }
}

class YourApplication extends \Cspray\Labrador\AbstractApplication {
    private $service;
    private $emitter;
    
    public function __construct(\Cspray\Labrador\Plugin\Pluggable $pluggable, \Cspray\Labrador\AsyncEvent\Emitter $emitter, HelloWorldService $service) {
        parent::__construct($pluggable);
        $this->service = $service;
        $this->emitter = $emitter;
    }
    
    public function execute() : \Amp\Promise {
        return \Amp\call(function() {
            yield $this->emitter->emit(new \Cspray\Labrador\AsyncEvent\StandardEvent('my-app.custom-event', $this));
            $output = yield $this->service->sayIt();
            print($output . PHP_EOL);
        });
    }
}

?>
```

You'll notice that there's a dependency required by the implementation that is required by the AbstractApplication; the 
Pluggable at the first constructor param. Managing Plugin operations is a task delegated to the PluginManager implementation 
and if your Application instance is created by the provided Injector this will be taken care of for you. <b>It is very 
important that the PluginManager implementation is used as the delegated Pluggable to ensure your Plugins are loaded 
correctly.</b>

If it is not obvious from the code above our Application will emit an Event, allowing all listeners 
to respond. It will then retrieve a value from a service, in this case a simple string that resolves 
immediately, and output that value to stdout. If an exception is thrown it will simply be rethrown 
and your Application will exit in an errored state.

#### Executing your Application

Now that you have your Application defined you need to run it. Running an Applicatino requires that 
you have an Engine capable of running it. Fortunately, out of the box Labrador comes with a 
`DependencyGraph` object that will allow you to create an appropriate Engine implementation.

{% include message.html
    message_type="is-info"
    title="App Executable"
    body="If your app was created using the labrador-app-skeleton you should execute your Application with the provided `app` executable. The below example is meant to demonstrate the minimum required to execute a contrived example."
%}


```php
<?php

$injector = (new \Cspray\Labrador\DependencyGraph())->wireObjectGraph();

$app = $injector->make(YourApplication::class, [':service' => new HelloWorldService()]);
$engine = $injector->make(\Cspray\Labrador\Engine::class);

$engine->run($app);
?>
```

That's all there is to it. Typically you would have far more than 1 object in your own object graph 
and there would be more to your Application setup. Depending on how your application is structured this could simply 
be adjusting a single bootstrap file, a configuration, or your app's DependencyGraph.

Next you should take a look at Plugins to learn how to build reusable, encapsulated functionality that can be shared 
across multiple Applications or be split out of the primary application repo with easy hooks for interacting with your
Application.


{% include next_previous_article_nav.md 
   previous_article_name="Configuration"
   next_article_name="Plugins"
%}

