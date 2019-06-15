---
layout: docs
---
## Application Documentation

The `Application` interface represents the part of your codebase that is the entrypoint for all of 
your business logic that falls outside the responsibility of Labrador. As such there are only 2 
responsibilities of an Application; executing the business logic of your codebase and determine 
how exceptions are to be handled when thrown during execution.

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

class YourApplication implements \Cspray\Labrador\Application {
    private $service;
    private $emitter;
    
    public function __construct(HelloWorldService $service, \Cspray\Labrador\AsyncEvent\Emitter $emitter) {
        $this->service = $service;
        $this->emitter = $emitter;
    }
    
    public function execute() : \Amp\Promise {
        return \Amp\call(function() {
            yield $this->emitter->emit(new \Cspray\Labrador\AsyncEvent\StandardEvent("my-app.custom-event0, $this"));
            $output = yield $this->service->sayIt();
            print($output . PHP_EOL);
        });
    }
    
    public function exceptionHandler(Throwable $throwable) : void {
        throw $throwable; 
    }
}

?>
```

If it is not obvious from the code above our Application will emit an Event, allowing all listeners 
to respond. It will then retrieve a value from a service, in this case a simple string that resolves 
immediately, and output that value to stdout. If an exception is thrown it will simply be rethrown 
and your Application will exit in an errored state.

#### Executing your Application

Now that you have your Application defined you need to run it. Running an Applicatino requires that 
you have an Engine capable of running it. Fortunately, out of the box Labrador comes with a 
`DependencyGraph` object that will allow you to create an appropriate Engine implementation.

```php
<?php

$injector = (new \Cspray\Labrador\DependencyGraph())->wireObjectGraph();

$app = $injector->make(YourApplication::class, [':service' => new HelloWorldService()]);
$engine = $injector->make(\Cspray\Labrador\Engine::class);

$engine->run($app);
?>
```

That's all there is to it. Typically you would have far more than 1 object in your own object graph 
and there would be more to your Application setup. Please check out our [Conventions](conventions) 
for more information about this subject. Otherwise you should continue within learning more about 
the Engine running your Application.

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