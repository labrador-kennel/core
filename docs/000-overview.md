# Overview

Labrador is a library to help wire together small-to-medium sized PHP7 applications. Functionality is generally provided by 
third-party libraries with Labrador being the glue that brings everything together. This is a "low-level" library. It is 
intended for environment-specific microframeworks be built on top of the modules provided by Labradir. As such, we gear the 
modules to be applicable to the vast majority of applications.
 
## Conventions
 
We don't enforce any conventions on you. While conventions are important I don't presume to know what's appropriate for your 
project or development team. I've tried to make the library and its interfaces simple to use, easy to implement and flexible 
enough to suit your needs. That being said to take advantage of what Labrador offers there are some development philosophies 
we encourage or that you should be made aware of.

### What you want to do happens in an Event

Labrador is as much an event emitter as it is anything else. A series of events are triggered and your application or library 
is expected to listen to these events to provide functionality. There's more details further in the documentation but the 
following situations trigger an event;

- Environment is initialized, including Plugins loaded
- Application logic should be executed
- Any cleanup necessary should be executed, this will be ran even when an exception is thrown
- Exception was thrown in any of the above scenarios and should be handled

### Dependency Inject everything

I am a firm proponent of dependency injection; Labrador's flexibility simply could not be possible without proper use of 
dependency injection. Instead of rolling our own simple container we use a highly robust, advanced container that makes 
wiring your application's object graph a straightforward task. That container is [Auryn](https://github.com/rdlowrey/auryn); 
it is highly recommended you check out how it works.

### Many Small Libraries over One Big Framework

There are a lot of great PHP developers out there making great PHP libraries. Instead of building one monolithic framework 
I believe it is better to glue together specific-built libraries. Before functionality is implemented you should review 
the available libraries and make sure the functionality isn't already provided. Small libraries can be framework-independent 
and that's something we should be striving for as PHP developers.
