# Creating your DependencyGraph

Sometimes it doesn't make sense to add a dependency to the Injector as a Plugin. The dependency may be too coupled to 
your applciation or simply doesn't make sense to share outside of the context of your application. Additionally, these 
type of services may be required by your Application itself. If this is the case do not simply implement an `InjectorAwarePlugin`. 
Instead, implement a `DependencyGraph` that knows how to integrate with Labrador.

How you wire up your dependencies into the `Injector` for your `Application` is not something explicitly defined by an 
interface or even an implementation provided by Labrador. What we describe below is what we reccommend, but you could 
just as easily put your dependency graph wiring directly into your app bootstrap code. We don't recommend this to 
discourage your bootstrap code from potentially changing a lot over time; bootstrap code should eventually stabilize 
and not have to be adjusted when adding new functionality.

```php
<?php

namespace Acme;

use Auryn\Injector;
use Cspray\Labrador\DependencyGraph as LabradorDependencyGraph;

class DependencyGraph {

    private $labradorGraph;

    public function __construct(LabradorDependencyGraph $labradorGraph) {
        $this->labradorGraph = $labradorGraph;
    }

    public function wireObjectGraph() : Injector {
        $injector = $this->labradorGraph->wireObjectGraph();

        // Adjust the graph for $injector

        return $injector;
    }

}
```

After you have wired up your object graph the last step is to replace the `Cspray\Labrador\DependencyGraph` instance 
used in your bootstrap code with your new instance.

### Next Steps

Next up you can learn about [Implementing Custom Plugin Types][custom-plugin-types] or you can take a [Deep Dive: Plugins][plugins-deep-dive].

[custom-plugin-types]: /docs/core/how-tos/implementing-custom-plugin-types
[plugins-deep-dive]: /docs/core/references/plugins-deep-dive
