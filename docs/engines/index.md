---
layout: docs
---
## Engine Documentation

An Engine is an interface where the primary responsibility is running an Application. It is also required to provide 
an Emitter that is used to emit events pertaining to the Engine as well as keep track of what state it is in
(idle, running, or crashed). In most use cases you would rarely, if ever, interact with the Engine directly outside of 
passing your Application instance to `Engine::run` so the rest of this documentation touches briefly on the process the 
default Engine implementation goes through and should be used as a baseline for your own Engine implementation if you 
choose to create your own.

### Engine Running Process

1. Make sure the Engine is idle. Trying to start an Engine that is running or has crashed should result in an Exception.
1. Set an error handler on the Loop to ensure any exceptions that bubble up will be logged and passed to the Application's exception handler. Steps should be taken to ensure the `Engine::SHUT_DOWN_EVENT` still fires if possible.
1. Emit an `Engine::START_UP_EVENT` and ensure `Application::loadPlugins` is executed.
1. Execute the Application
1. Emit an `Engine::SHUT_DOWN_EVENT`

If you are implementing your own Engine instance it is highly recommended that you review the AmpEngine source for 
more detailed guidance.

{% include next_previous_article_nav.md 
   previous_article_name="Applications"
%}