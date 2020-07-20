# Deep Dive: Plugins & Pluggables

This document details the Plugin loading process specifying each step that happens and in what order those steps occur.
If you implement your own Pluggable you should thoroughly review and understand this loading process. The Pluggable 
is responsible for this process and not adhering to the details specified here could result in Plugins that don't 
operate correctly.

This guide talks in detail especially about the `Pluggable` interface. The actual implementation that is used for this 
interface SHOULD always be the `PluginManager` implementation provided out-of-the-box. If you need to implement the 
`Pluggable` interface you should delegate those operations to a `PluginManager` instance.

> The rest of this guide assumes that you have read _all_ the [Plugin related tutorials](/docs/core/tutorials)

### Register Plugins as class names

Labrador encourages Dependency Injection and sharing common services through `InjectorAwarePlugin` implementations. If you  
depend on one of these services in a constructor of a Plugin there's a chicken and egg problem where the dependent 
Plugin needs to register it service with the Injector in the bootstrapping process but the Plugin loading process doesn't 
happen until the engine starts running.

To solve this problem we simply remove the need to instantiate any Plugin during the bootstrapping process. Instead, Plugin 
instantiation happens using the `Injector` that's already present within the PluginManager to satisfy the requirements of the 
`InjectorAwarePlugin`. 

### The Loading Process

Plugins are loaded, generally, in the order that they are added to the Pluggable. However, if one of the Plugins implements 
`PluginDependentPlugin` any Plugin that it depends on will go through its entire loading process first. Dependent Plugins 
MUST be explicitly registered with the Pluggable, or an exception will be thrown.

The specific steps of the loading process includes...

1. If the Plugin implements `PluginDependentPlugin` then each dependent Plugin completes this entire process first... to include this step.
1. If the Plugin implements `InjectorAwarePlugin` the Injector is provided to it so its object graph can be defined.
1. If the Plugin implements `EventAwarePlugin` the EventEmitter is provided to is so any event listeners can be registered.
1. If the Plugin implements any type that is registered as a custom Plugin loader that custom handler is invoked
1. If the Plugin implements `BootablePlugin` its boot method is invoked.

It is important to note that a circular dependency is possible if a `PluginDependentPlugin` depends on a Plugin that in turn 
depends on it. The Pluggable implementation MUST take into account this possibility and short-circuit plugin loading to 
prevent a runaway application from occurring.

### Removing a Plugin

It is also possible to remove a Plugin sometime after it has been loaded. There's nothing especially noteworthy about 
removing a Plugin, it simply provides an opportunity to perform some cleanup procedure. Removing a Plugin is not intended 
to be complicated and should not involve asynchronous code.
