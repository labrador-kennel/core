---
layout: docs
---
## Core Documentation

The documentation for Labrador Kennel Core so that you can become familiar with developing for the internal packages
or creating your own applications on top of Labrador Kennel. This package contains all of the "low-level" concepts
including Plugins, Applications, and Engines.

### Async by design

It is important to remember that all Labrador Kennel packages are asynchronous by design unless explicitly noted
in the package documentation. Asynchronous applications require a different way of thinking if you've only ever
developed synchronous PHP applications. It is highly recommended that you take a look through, and understand,
[Amp] before continuing with the rest of this documentation. The rest of this guide assumes that you understand how 
Promises work and how to resolve Promises on the Event Loop.

### Embrace Dependency Injection

Labrador uses [Dependency Injection] throughout its codebase and encourages your application to do the same. The 
complexities behind providing and sharing dependencies is taken care of by the [Auryn] IoC container. Auryn does not 
work like other PHP containers. You should understand the power it provides and how to wire an object graph with it 
before developing on internal packages or creating applications on top of Labrador Kennel.

You should severely restrict the amount of code that requires the Auryn container to be injected into it. You
effectively make your container a [Service Locator] which highly detracts from the value of Dependency Injection. 
Generally speaking there are very few times where you should be required to pass an Injector to a constructor, object 
factories would be a notable exception. For example, the `PluginManager` requires an Inject be passed in the constructor 
to satisfy the requirements of the `InjectorAwarePlugin`. However, the Injector is explicitly defined as a PluginManager 
constructor dependency and is not simply shared with itself.

<hr />

<a href="plugins/index.html" class="is-pulled-right is-size-5">Plugins <span class="icon"><i class="fas fa-angle-right"></i></span></a>

[Amp]: https://amphp.org
[Auryn]: https://github.com/rdlowrey/auryn
[Dependency Injection]: https://stackoverflow.com/a/130862
[Service Locator]: http://blog.ploeh.dk/2010/02/03/ServiceLocatorisanAnti-Pattern/