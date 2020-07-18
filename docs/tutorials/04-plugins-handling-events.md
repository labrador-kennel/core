# Plugins: Handling Events

Events are handled by a separate Labrador package, [async-event]. This library includes an `EventEmitter` interface and 
implementations that allow attaching and detaching listeners that are invoked when corresponding events are emitted. The 
event listeners run in context of [Amp's `Loop`][amp-loop], which allows your listeners to yield Promises and resolve 
asynchronous code in response to an event! We'll go over how to use the `EventAwarePlugin` to easily interact with this 
library.

### Implementing `EventAwarePlugin`

### Next Steps

{% include core/plugin_next_steps.md hide='events' %}

[async-event]: /async-event
[amp-loop]: https://amphp.org