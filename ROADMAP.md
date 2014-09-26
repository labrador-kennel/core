# Roadmap

Details the timeline for the Labrador application and when we expect specific functionality to be available.

## 0.1.0 release

- Initial release
- Provides Routing layer with FastRouter wrapper and robust set of HandlerResolver implementations
- Fully tested Application implementation with appropriate event triggering

## 0.2.0 release

- `ControllerInvoker` interface to allow changing the way the controller is invoked
- `DefaultControllerInvoker` that will invoke the controller and pass a `Request` argument
- `AurynControllerInvoker` that will utilize `Auryn\Injector::execute` to invoke the controller

## 0.3.0 release

- 100% code coverage
