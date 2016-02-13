# Changelog

## v1.1.0 - 2016-02-13

- **BC BREAK** Removes UnsupportedOperationException and EventStub as they were not used in the codebase
- Adds support for classes extending CoreEngine to append arguments passed to listeners for all triggered events.
- Fixes a bug where the Engine::ENGINE_BOOTUP_EVENT could possibly be triggered multiple times if `CoreEngine::run()` is
  called multiple times.
- Fixes README to no longer reflect the Telluris library which is no longer used in the codebase

## v1.0.0 - 2016-01-13

- **BC BREAK** Removes SafeHashMap and ImmutableSafeHashMap as they were not used in the codebase
- Moves test suite to PSR-4 autoloading
- Cleans up use statements to use PHP7 syntax
- Fixes a couple typos

## v0.3.1 - 2016-01-08

- Update dependencies to use Whoops 2.0 over dev-master

## v0.3.0 - 2015-12-17

- **BC BREAK** Removes Telluris dependency
- **BC BREAK** Renames EnvironmentInitializeEvent -> EngineBootupEvent
- Moves autoloading from PSR-0 to PSR-4

## v0.2.0 - 2015-08-17

- **BC BREAK** Removes following classes:
    - Cspray\Labrador\ErrorToExceptionHandler
    - Cspray\Labrador\UncaughtExceptionHandler
- Adds `Cspray\Labrador\bootstrap()` function that will set error and exception handlers
  with Whoops and create an Auryn\Injector with Labrador HTTP's required services.
- Updates Telluris version
- Uses shields.io for README badges

## v0.1.1 - 2015-08-16

- Updates Ardent to use morrisonlevi repo. Installing requies --ignore-platform-reqs due to
  PHP7 requirements.
- Removes the travis.phpunit.xml.dist and changes Travis to use phpunit.xml.dist

## v0.1.0 - 2015-08-16

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
