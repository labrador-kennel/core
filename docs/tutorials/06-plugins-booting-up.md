# Plugins: Booting Up

Sometimes your Plugin might need to do something one time during the Plugin loading process. This is where the 
`BootablePlugin` interface comes into play. Implementing this interface ensures that your Plugin has an opportunity to 
complete its task after all other Plugin loading procedures have finished.

On the surface implementing the `EventAwarePlugin` and listening to the `Engine::START_UP_EVENT` is the same as 
implementing `BootablePlugin`. A slight difference is that the Plugin loading process happens _before_ the 
`Engine::START_UP_EVENT` is emitted. The impact this has on your running application is that the `BootablePlugin` will 
execute its boot method _as soon as the individual Plugin is done loading_. The `EventAwarePlugin` listening to `Engine::START_UP_EVENT` 
will only execute _after all Plugins have finished loading_. There's also simply less boilerplate with the BootablePlugin.

### Implementing BootablePlugin

Continuing our theme of logging stuff in examples we'll log out a message when our Plugin is booted. It is important to 
note that the boot method is the very last thing that is executed during Plugin loading.

```php
<?php

namespace Acme;

use Amp\Promise;
use Cspray\Labrador\Plugin\BootablePlugin;
use Psr\Log\LoggerInterface;
use function Amp\call;

class BootLoggingPlugin implements BootablePlugin {

    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function boot() : Promise {
        return call(function() {
            $this->logger->info('Our Plugin has finished booting!'); 
        }); 
    }

}
```

### Next Steps

If you find yourself writing a Plugin that requires services another Plugin provides you may want to take a look at 
[Plugins: Depending On Other Plugins](/docs/core/tutorials/plugins-depending-other-plugins).
