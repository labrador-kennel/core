# Plugins: Depending on other Plugins

Sometimes you need to rely on a service provided by another `Plugin` or need to ensure that Plugin has done some other 
thing before your Plugin will work correctly. Implementing the `PluginDependentPlugin` will ensure that any other 
Plugins you depend on will be loaded first.

### Implementing PluginDependentPlugin

The very first thing that happens when a Plugin begins its loading process is to ensure that any other Plugins it may be 
dependent on is finished with its own loading process. In our example we're going to create a Plugin that implements 
`InjectorAwarePlugin` that originates from a third-party library. We want to do something with the service that gets 
registered. By depending on the Plugin that provides the service we can be sure it is available in the `Injector` when 
our Plugin begins its loading process.

```php
<?php

namespace Acme\ThirdParty {

    use Cspray\Labrador\Plugin\InjectorAwarePlugin;
    use Auryn\Injector;

    interface BarService {}

    class StandardBarService implements BarService {}
    
    class ThirdPartyServicePlugin implements InjectorAwarePlugin {
    
        public function wireObjectGraph(Injector $injector) : void {
            $injector->share(BarService::class);
            $injector->alias(BarService::class, StandardBarService::class);
        }
    
    }

}

namespace Acme\MyApp {

    use Cspray\Labrador\Plugin\BootablePlugin;
    use Cspray\Labrador\Plugin\PluginDependentPlugin;
    use Amp\Promise;
    use Acme\ThirdParty\ThirdPartyServicePlugin;
    use Acme\ThirdParty\BarService;
    use function Amp\call;

    class MyPlugin implements BootablePlugin, PluginDependentPlugin {

        private $barService;

        public function __construct(BarService $barService) {
            $this->barService = $barService; 
        }

        public static function dependsOn() : array {
            return [ThirdPartyServicePlugin::class];
        }

        public function boot() : Promise {
            return call(function() {
                // We can do something with $this->barService now
            });
        }

    }

}
```

Your Plugin doesn't _have_ to implement the `boot()` method. It is simply there to show that you can declare constructor 
dependencies that are provided by the dependent Plugin. They will be injected automatically during the loading process 
for your Plugin.

An astute observer would notice that this interface introduces a possibility for a circular dependency and an infinite 
loop situation. The intricacies of the Plugin loading process, especially handling this interface, is tested and 
well-handled by the `PluginManager` implementation... including the problems with circular dependencies. You should let 
the `PluginManager` implementation handle the Plugin loading process in the vast majority of use cases.

### Next Steps

Next up you should continue to learn more about [Creating your DependencyGraph][creating-your-dependencygraph].

[creating-your-dependencygraph]: /docs/core/how-tos/creating-your-dependency-graph
