# Changelog

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

## v0.1.1 - 2015-08-16

- Updates Ardent to use morrisonlevi repo. Installing requies --ignore-platform-reqs due to 
  PHP7 requirements.
- Removes the travis.phpunit.xml.dist and changes Travis to use phpunit.xml.dist

## v0.2.0 - 2015-08-17

- **BC BREAK** Removes following classes:
    - Cspray\Labrador\ErrorToExceptionHandler
    - Cspray\Labrador\UncaughtExceptionHandler
- Adds `Cspray\Labrador\bootstrap()` function that will set error and exception handlers
  with Whoops and create an Auryn\Injector with Labrador HTTP's required services.
- Updates Telluris version
- Uses shields.io for README badges

## v0.2.1 - TBD

*Reserved for bug fixes*

## v0.3.0 - TBD

*Features to come*