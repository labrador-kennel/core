# Roadmap

Details the timeline for the Labrador application and when we expect specific functionality to be available.

## 0.1.0 release

- Initial release
- Provides Routing layer with FastRouter wrapper and robust set of HandlerResolver implementations
- Implementation of HttpKernelInterface that emits 4 events (`ApplicationHandle`, `BeforeController`, `AfterController`, `ApplicationFinish`
- Event driven excception handling by setting a response to the `ExceptionThrown` event.

## 0.2.0 release

- Labrador Plugin to encapsulate common functionality.

## 0.3.0 release

- `ControllerInvoker` interface to allow changing the way the controller is invoked
- `DefaultControllerInvoker` that will invoke the controller and pass a `Request` argument
- `AurynControllerInvoker` that will utilize `Auryn\Injector::execute` to invoke the controller
