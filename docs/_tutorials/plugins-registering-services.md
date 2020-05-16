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



[Auryn]: https://github.com/rdlowrey/auryn