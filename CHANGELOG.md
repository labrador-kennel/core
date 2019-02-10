# Changelog

## 3.0.0-rc1 - 2019-02-??

#### Added

- Adds a `CODE_OF_CONDUCT.md` which directs users to the Labrador Governance repository.

#### Changed

- Updates the code style to match the Labrador Coding Standard. 
- Changed the Contributing guide to point to the Labrador Governance repository.

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
