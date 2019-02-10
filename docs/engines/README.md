# Labrador Engines

If an Application is all of your dependencies and app execution then the Engine is what 
runs the Application. It brings everything together and actually kicks off the process 
of invoking `Application::execute()`.

##  Engine

While the Application takes care of your business logic the Engine is an encapsulation of 
the event loop execution necessary for your Application to run and acts as the primary Pluggable 
that ensures Plugins can interact with the appropriate Labrador provided interfaces and that 
those Plugins are initiated correctly. The Engine interface requires the following contract:

```php
<?php

namespace Cspray\Labrador;

interface Engine extends Plugin\Pluggable {
    public function run(Application $application) : void;
    
    public function getEmitter() : AsyncEvent\Emitter;
}
```

The `Engine::run` method is analogous to amphp's `Loop::run` method; the key difference being 
that Labrador's Engine requires an `Application` instance instead of a callable function.

The `Emitter` is the instance that powers all events for that Engine. Engine implementations MUST
trigger the following events.

- `labrador.engine_bootup` 

    Should be triggered ONCE AND ONLY ONCE the first time that an Application is passed to `Engine::run()`. 
    While it is not an expected use case that `Engine::run()` will be invoked more than one time for the life of 
    the running process the implementation MUST account for the possibility.
    
- `labrador.app_cleanup`

    Should be triggered ONCE AND ONLY ONCE after the Application has finished executing but before the 
    event loop has completey stopped. It is expected after this event is triggered the Engine will stop 
    running when all listeners have resolved their respective Promise.
    
## Implementing an Engine

Currently we have a well-tested engine running on amphp's `Loop`; the `CoreEngine`. This implementation _should_
be sufficient for your needs. However, if not please review the `CoreEngine` implementation as a reference. Additionally,
it is highly recommended you utilize the existing `PluginManager` implementation as proper loading of Plugins is easily the 
most complicated aspect of implementing an Engine.