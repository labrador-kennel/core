# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 3.2.0 - 2020-??-??

#### Added

- An `ApplicationEnvironment` enum that determines the environment of the host machine running your Labrador app.
- An `Environment` interface and implementations that encapsulates access to the `ApplicationEnvironment` and 
environment variables that exist on the host machine.
- A `Settings` interface and implementation that allows for providing configuration details for both Labrador and 
your app.
- A `SettingsLoader` and `SettingsStorageHandler` interface and implementations that allow fine-grained control on how 
the `Settings` for your application are generated. Comes out of the box with support for both PHP and JSON settings on 
the local filesystem. You should read over /docs/tutorials/02-application-settings.md
- An `ApplicationObjectGraph` interface that will facilitate more robust bootstrapping code in the future. An instance, 
the `CoreApplicationObjectGraph`, takes over the responsibilities of the `DependencyGraph` while also providing in the 
injector an `Environment` instance and, an optional, `Settings` instance.

#### Deprecated

- The `DependecyGraph` is deprecated and will be removed in the next major release. Users should transition to use the 
`CoreApplicationObjectGraph` instead.

## 3.1.0 - 2020-08-02

#### Added

- Added a protected method `AbstractApplication::logException` that logs detailed information about an exception as well 
as additional application specific information as context to the Logger.

## 3.0.0 - 2020-08-02

#### Added

- Added convenience methods on to `ApplicationState` and `EngineState` to make it easier to determine if either one is 
in a specific state.

#### Fixed

- Fixed a problem in `AmpEngine` where an exception thrown in an event listener to the `Engine::SHUT_DOWN_EVENT` would 
result in a memory leak and subsequent hard crash of the application as it went into an infinite loop invoking the Loop's 
error handler over and over again.

#### Changed

- Simplified the contents of the in-repo documentation to facilitate easier in-repo use and to allow integration with 
the new website repo powering labrador-kennel.io.

## 3.0.0-beta7 2019-11-03

#### Added

- Adds `Application::getState` which returns an enum `ApplicationState` signifying whether the Application is Started, 
Stopped, or Crashed.

#### Changed

- The DependencyGraph object now expects you to provide a Logger implementation as a constructor dependency instead of 
the DependencyGraph creating the Logger object based off of a configuration.

#### Removed

- Removed the Configuration interface and corresponding ConfigurationFactory. In practice this Configuration was 
tied to a process for providing an out-of-the-box solution for invoking Applications that was clunky and not well 
thought out. For now instead of moving forward with a sub-optimal solution each app will need to provide its own 
boilerplate for executing the app. As more experience is gathered in running real-life apps on this framework we 
may revisit the Configuration concept.
- Removed the shell script that created a rough app skeleton. More thought needs to go into how this would work before 
it is released live.

## 3.0.0-beta6 2019-11-02

#### Fixed

- Fixes the DependencyGraph aliases to ensure that the appropriate services are shared and aliased to the correct 
default implementation.

## 3.0.0-beta5 2019-11-02

This release represents a major refactor to the Plugin system in an attempt to make the more common use 
case easier to facilitate and to provide more async support for Plugins.

#### Added

- Adds the `cspray/yape` library to create type-safe, powerful enums. Adds a new EngineState 
enum.
- Adds the `amphp/log` library to facilitate asynchronous logging using Monolog\Logger.
- `Pluggable::registerPluginRemoveHandler` was added that allows invoking a custom function whenever a 
Plugin is removed AFTER the loading process has been completed. These handlers will not run if the Plugin 
is removed before loading is initiated.
- `Pluggable::havePluginsLoaded` was added to determine whether or not Plugins have gone through the 
loading process.
- `Pluggable::getLoadedPlugin` was added to retrieve a Plugin by name once the loading process has taken 
place. Attempting to call this method before loading has occurred will result in an exception.
- `Pluggable::getLoadedPlugins` returns a collection of all loaded Plugins. Attempting to call 
this method before loading has occurred will result in an exception.
- Added `EventAwarePlugin::removeEventListeners` that will be invoked whenever a loaded Plugin is 
removed from its Pluggable.
- Added the Monolog library and implemented PSR-3 logging throughout the provided Engine and PluginManager.
Additionally the DependencyGraph object has been setup so that any object implement the LoggerAwareInterface 
will automatically have the appropriate Logger set to it.
- Added the `Engine::getState` method that normalizes the idea of an Engine having a state and allows 
consuming code to determine that state with a known API.
- Added the `LoggerAwareInterface` to `Application` so that all apps can easily log whatever data is necessary 
for their execution.
- Added the `Configuration` interface and the ability to load a Configuration that will help determine how Labrador
works out-of-the-box. This configuration can be written in native PHP, JSON, or XML. Please checkout the Configuration 
docs for more information.
- Introduces a `labrador-app-skeleton` binary that is meant to easily get started with Labrador by creating some 
boilerplate configuration, Application skeleton, and a DependencyGraph.

#### Changed

- The `Engine::getState` method now returns an EngineState enum as opposed to an arbitrary string.
- The DependencyGraph implementation now expects a Configuration instance as its only constructor 
dependency. A Monolog\Logger will be created using the configured log name and log path with a 
Amp\Log\StreamHandler as the only registered handler. Additional handlers may be registered
- `Pluggable::registerPlugin(Plugin)` was changed to `Pluggable::registerPlugin(string)` where the string
is the fully qualified class name of a type that implements the Plugin interface. This was done to more 
easily facilitate the use case where a Plugin may depend on a service to be constructed.
- `Pluggable::registerHandler` was changed to `Pluggable::registerPluginLoadHandler` to differentiate it from 
the newly added remove handlers.
- `Pluggable::hasPlugin` was changed to `Pluggable::hasPluginBeenRegistered` to more explicitly state what 
is being checked with the new differentiation between registering and loading a Plugin.
- `Pluggable::getPlugins` was changed to `Plugglable::getRegisteredPlugins` to be more semantic on 
what is being returned. This will always be a collection of Plugin names.
- Changed the invocation of Pluggable load handlers to support resolving Promises.
- Changed the `Engine` interface to no longer extend `Pluggable`.
- An Application is no longer a Plugin of any type as the expected use case for an Application does 
not work well with the Plugin loading process.
- Moved the `PluginManager` implementation into the `Cspray\Labrador\Plugin` namespace.
- Changed the `BootablePlugin::boot` method to return a `Promise` instead of a callable now that 
services may be injected as a constructor dependency.
- Changed the `PluginDependentPlugin::dependsOn` method to be static so that dependencies can be 
provided before the Plugin is instantiated.
- Changed the Plugin loading process such that a Plugin dependency does not need to be registered 
to complete the loading process. The Plugin dependency need only be able to be instantiated by the 
Injector.
- Changed the name of the `Engine::ENGINE_BOOTUP_EVENT` -> `Engine::START_UP_EVENT` to be more consistent 
with its accompanying event.
- Changed the name of the `Engine::APP_CLEANUP_EVENT` -> `Engine::SHUT_DOWN_EVENT` to be more 
semantic with its counterpart.

#### Removed

- Removed the `PluginDependencyNotProvidedException` and replaced its uses with either an 
`InvalidStateException` or an `InvalidArgumentException`.
- Removed the `StandardApplication` in favor of a more robust series of Application implementations 
out of the box.

## 3.0.0-beta4 2019-05-11

The previous 3.0 Release Candidate has been found lacking key features that should be implemented for 
a stable release. The codebase will become more stable before a 2nd RC is released.

#### Added

- A new method `Pluggable::loadPlugins` that must be explicitly called to load Plugins. Additionally, 
this method is expected to be asynchronous to take advantage of async Plugin booting.

#### Changed

- Refactors the `Bootable::boot` method to return a callable that can be invoked in context of the 
event loop, meaning you can yield Promises etc, and has all dependencies resolved with your Injector.
- Refactors the Pluggable::registerPlugin method to return void and to throw an exception if a Plugin
is attempted to be registered after `Pluggable::loadPlugins` is called.
- Changed the `InvalidEngineStateException` to an `InvalidStateException` to be more generic and 
used in multiple places.
- Renamed `ServiceAwarePlugin` -> `InjectorAwarePlugin` and renames the method on this interface to 
`wireObjectGraph`. This ensures we are not conflating the term "Service" with other possible meanings 
in your application, is more explicit to consumers about intent, and matches the naming strategy 
for the `DependencyGraph` object convention.

## 3.0.0-rc1 - 2019-02-16

#### Added

- Adds a `CODE_OF_CONDUCT.md` which directs users to the Labrador Governance repository.
- Improved the documentation around Plugins and Engines.

#### Changed

- Renames `CoreEngine` -> `AmpEngine` to make it clear which event loop implementation is 
powering the given Engine.
- Renames `Services` -> `DependencyGraph` to be more clear what the intent of the object is 
as well as to maintain consistency with other Labrador packages.
- DependencyGraph will no longer share the Injector with itself to steer users away from using 
the Injector as a Service Locator.
- Updates the code style to match the Labrador Coding Standard. 
- Changed the Contributing guide to point to the Labrador Governance repository.

#### Removed

- Removed the `bootstrap()` function. You should now invoke `DependencyGraph::wireObjectGraph()` 
directly instead.
- Removed the `filp/whoops` library as a required dependency. If you wish to retain this error 
handling functionality you will be required to add this dependency directly to composer.json.

## v3.0.0-beta3 - 2019-01-19

#### Fixed

- Fixed a bug in the `PluginManager` handling custom Plugin types that are instances of an 
interface. Previously only custom Plugins that were direct instances of the custom type would 
properly be loaded. Now all custom Plugin handlers will properly be invoked, even for Plugins 
implementing an interface for a register Plugin handler.

## v3.0.0-beta2 - 2019-01-13

#### Added

- Added a `Pluggable::registerPluginHandler` method that allows developers to assign their 
own custom function to be executed when a Plugin of a specific type is initialized. This 
custom handler executes after the plugin's dependencies, services, and events have been 
registered but _before_ the `Plugin::boot` method is called. Not only can you assign additional 
handlers to Plugins provided by Labrador out of the box but you can assign your own 
handlers to Plugins that you provided specifically for your Application or project.

## v3.0.0-beta - 2018-01-14

**This release represents a major BC Break as we incorporate Amphp's Event Loop support 
and move to an async architecture.** It should be assumed that most items below will 
represent a break in previous versions.

#### Added

- Added new `Application` interface to act as one conventional place to configure an 
  app's services and event listeners. It also acts as the primary form of execution for 
  your app with the `Application::execute()` method that returns a Promise that resolves 
  when the app is done executing and wishes to close.
- Added the amphp/amp 2.0 library.

#### Changed

- The `CoreEngine` instance now runs inside an Amp Event Loop. All applications running 
  inside a Labrador Engine are expected to be asynchronous in nature.
- Refactored the `Engine::run()` method to require an `Application` as the first and 
  only argument.
- Changed the `Plugin\Pluggable::getPlugins()` method to have a return type of iterable
  as opposed to forcing the return of an array.
- Changed methods that are not expected to return anything to return `void`. All method 
  signatures should now include a return type.
  
#### Removed

- Removed the league/event library in exchange for cspray/labrador-async-event.
- Removes all custom event types in exchange for emitting StandardEvents provided by 
  labrador-async-event.
- Removed the concept of an AppExecute event; processing that should occur at the time
  of application execution should be handled in your individual Application instances.

## v2.0.0 - 2016-03-20

#### Changed

- Updates Auryn to 1.4.0
- Updates Whoops to 2.1.0

#### Removed

- **BC BREAK** Removes the `Plugin::boot` method and introduces a new `BootablePlugin` interface. In practice very few
  Plugins actually needed to use the `boot` method. If your Plugin *does*  make use of this method you'll need to make 
  sure that you implement this new interface otherwise your Plugin **WILL NOT** boot. If you *don't* make use of this 
  method you can now remove the useless code from your codebase.

#### Fixed

- Fixes deprecated uses of `setExpectedException` in test suite.

## v1.2.1 - 2016-03-13

#### Changed

- Updates Auryn to 1.2.
- Updates several dev dependencies.
- Updates README.md to appropriately reflect move to labrador-kennel organization.

## v1.2.0 - 2016-02-14

#### Added

- Adds the ability to create a custom event object with the StandardEventFactory through the `StandardEventFactory::register(eventName, factoryFn)` 
  method. The `factoryFn` MUST return an instance of `League\Event\EventInterface` with a name that matches `eventName`. An 
  exception will be thrown if an invalid value is returned.

#### Changed

- **BC BREAK** Renames the `Services::createInjector` to `Services::wireObjectGraph` and allows the passing of an Auryn\Injector 
  that services will be added to instead of simply creating a service container.

- Minor cleanup of composer.json impacting require-dev and suggests

## v1.1.0 - 2016-02-13

#### Changed

- Adds support for classes extending CoreEngine to append arguments passed to listeners for all triggered events.

#### Removed

- **BC BREAK** Removes UnsupportedOperationException and EventStub as they were not used in the codebase

#### Fixed

- Fixes a bug where the Engine::ENGINE_BOOTUP_EVENT could possibly be triggered multiple times if `CoreEngine::run()` is
  called multiple times.
- Fixes README to no longer reflect the Telluris library which is no longer used in the codebase

## v1.0.0 - 2016-01-13

#### Fixed

- Moves test suite to PSR-4 autoloading
- Cleans up use statements to use PHP7 syntax
- Fixes a couple typos

#### Removed

- **BC BREAK** Removes SafeHashMap and ImmutableSafeHashMap as they were not used in the codebase

## v0.3.1 - 2016-01-08

#### Changed

- Update dependencies to use Whoops 2.0 over dev-master

## v0.3.0 - 2015-12-17

#### Changed

- **BC BREAK** Renames EnvironmentInitializeEvent -> EngineBootupEvent
- Moves autoloading from PSR-0 to PSR-4

#### Removed

- **BC BREAK** Removes Telluris dependency

## v0.2.0 - 2015-08-17

#### Added

- Adds `Cspray\Labrador\bootstrap()` function that will set error and exception handlers
  with Whoops and create an Auryn\Injector with Labrador HTTP's required services.

#### Changed

- Updates Telluris version
- Uses shields.io for README badges

#### Removed

- **BC BREAK** Removes following classes:
    - Cspray\Labrador\ErrorToExceptionHandler
    - Cspray\Labrador\UncaughtExceptionHandler

## v0.1.1 - 2015-08-16

#### Changed

- Updates Ardent to use morrisonlevi repo. Installing requies --ignore-platform-reqs due to
  PHP7 requirements.
- Removes the travis.phpunit.xml.dist and changes Travis to use phpunit.xml.dist

## v0.1.0 - 2015-08-16

#### Added

- Initial launch
- Engine interface and CoreEngine implementation
- Plugin system including: EventAwarePlugin, ServiceAwarePlugin, PluginDependentPlugin
- Event system with support for following events:
    - labrador.environment_initialize
    - labrador.app_execute
    - labrador.app_cleanup
    - labrador.exception_thrown
- Collection implementations that include: SafeHashMap, ImmutableSafeHashMap. A SafeHashMap
  returns null when a value is not present compared to an exception thrown from other
  Ardent maps.
- Dependencies including: Auryn, League/Event, Ardent, Telluris, Whoops
