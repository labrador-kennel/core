---
title: "Plugins: Registering Services"
order: 3
---
We anticipate registering services on to the `Auryn\Injector` being one of the most common use cases for Plugins. Labrador 
highly encourages libraries to be built that can work with _any_ Amp project. The `InjectorAwarePlugin` is simply some 
minimal glue to put your functionality into Labrador's ecosystem.

<div class="message is-info">
    <div class="message-body">
        Labrador does not provide its own container implementation, instead relying on the excellent <a href="https://github.com/rdlowrey/auryn">Auryn</a> 
        library. If you've never used Auryn before it is highly recommended you check out the documentation before continuing with this guide.
    </div>
</div>

### Implementing `InjectorAwarePlugin`

In our example we're going to assume there's a `Acme\Foo\BarService` that is an interface describing some functionality 
provided by your library. There's also a `Acme\Foo\StandardBarService` which acts as the default, out-of-the-box 
implementation for the `BarService`. Our plugin should ensure that only 1 instance of `BarService` is ever created and 
if you type-hint against `BarService` you should receive the `StandardBarService` implementation.

```php
<?php

namespace Acme\Foo;

use Cspray\Labrador\Plugin\InjectorAwarePlugin;
use Auryn\Injector;

final class BarServicePlugin implements InjectorAwarePlugin {

    public function wireObjectGraph(Injector $injector) : void {
        $injector->share(BarService::class);
        $injector->alias(BarService::class, StandardBarService::class);
    }

}
```

That's all there is to it! Obviously this is a simple example, your object graph may need much more advanced 
configuration. Fortunately [Auryn] is incredibly powerful and should be able to handle anything you might need  from it. 
Check out the Auryn documentation for more details.

### Not for your Application dependencies

The `InjectorAwarePlugin` is great if your functionality is very modular and can be used by different types of Labrador
applications. This plugin type is **not** suitable for the dependencies your `Application` requires directly. It also 
isn't well suited to dependencies that are overly coupled to your `Application` and wouldn't work easily in other Labrador 
applications. In these type of use cases you should take a look at [Creating Your DependencyGraph][create-dependency-graph]

### Next Steps

{% include core/plugin_next_steps.md hide='services' %}

[Auryn]: https://github.com/rdlowrey/auryn
[create-dependency-graph]: {{site.baseurl}}/how-tos/creating-your-dependency-graph