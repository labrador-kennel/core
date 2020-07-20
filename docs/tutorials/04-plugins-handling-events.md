# Plugins: Handling Events

Labrador emits semantic, data-rich events throughout the processing of an application to ensure that you can respond to 
meaningful occurrences while being decoupled from the code doing the actual processing. The [Labrador async-event](/docs/async-event)
library provides the functionality that emits events and allows you to attach listeners that respond to those events. This 
library allows for listeners to be processed in an async context and provides a wealth of functionality for working in 
async applications.

### Implementing EventAwarePlugin

In our example we're going to log out a message each time Labrador emits an out-of-the-box event.

```php
<?php

namespace Acme;

use Cspray\Labrador\AsyncEvent\Event;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\Engine;
use Cspray\Labrador\Plugin\EventAwarePlugin;
use Psr\Log\LoggerInterface;

class EventLoggingPlugin implements EventAwarePlugin {

    private $listenerIds = [];
    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function registerEventListeners(EventEmitter $emitter) : void {
        $this->listenerIds[] = $emitter->on(Engine::START_UP_EVENT, function(Event $event) {
            $this->logger->info(sprintf('We started up the Engine using %s', get_class($event->getTarget()))); 
        });
        $this->listenerIds[] = $emitter->on(Engine::SHUT_DOWN_EVENT, function() {
            $this->logger->info('The Engine is shutting down.');
        });
    }

    public function removeEventListeners(EventEmitter $emitter) : void {
        foreach ($this->listenerIds as $listenerId) {
            $emitter->off($listenerId);
        }   
    }

}
```

The `EventAwarePlugin` allows you to both register _and_ remove listeners from the `EventEmitter`. Registering event 
listeners happens as a normal course of Plugin loading and will happen automatically upon executing `Engine::run`. To 
have listeners removed the Plugin must be explicitly removed from the Pluggable using `Pluggable::removePlugin`.

### Next Steps

If you simply need to do something when your Plugin completes the loading process check out the [Plugins: Booting Up](/docs/core/tutorials/plugins-booting-up)
documentation next.
