# Events

A series of timely events is how your Labrador-powered application will be executed and how you can 
integrate with the library to do some really cool things. All of Labrador's event related functionality 
is provided by the exceedingly simple [Evenement](https://github.com/igorw/evenement). I encourage 
you to trigger your own domain-specific events within your application.

In this guide we talk about the events that are triggered by Labrador's out-of-the-box implementation. 
All events that are triggered are instances of `Cspray\Labrador\Event\Event`. Each event implements 
its own Event type that provides contextual information about the event being triggered. If you 
implement your own domain-specific events it is recommended you follow this practice.

## `labrador.environment_initialize`

Class: `Cspray\Labrador\Event\EnvironmentInitializeEvent`
Available objects: `Telluris\Environment`
    
An event triggered when the application first spins up. This is a good place to do any bootstrapping 
that your application might need. Labrador does the same thing by running any initializers you've 
set for the environment and then loading any plugins you've registered.

## `labrador.app_execute`

Class: `Cspray\Labrador\Event\AppExecuteEvent`
Available objects: None

Triggered after the environment has been initialized and your application is ready to take over. You 
should listen for this event and run your application's logic at that time.

## `labrador.app_cleanup`

Class: `Cspray\Labrador\Event\AppCleanupEvent`
Available objects: None

Triggered when your application is done processing, even if an exception is thrown by your application. 
Here you can free up any resources or do whatever you need to do when your application has finished 
processing.

## `labrador.exception_thrown`

Class: `Cspray\Labrador\Event\ExceptionThrownEvent`
Available objects: `Exception`

When your application, a library you use or maybe Labrador itself throws an exception an event will be 
triggered. Listen to this event and handle any exceptions thrown. If using Labrador directly it is 
highly recommended that you register a listener for this event. It is highly recommended that higher-level 
frameworks built on top of Labrador provide its own default listener for this event.

## Changing event types triggered by Labrador

It is possible to change the specific type of event objects that represents an event by providing an 
implementation of `Cspray\Labrador\Event\EventFactory`. While ths power is made available to you it 
is also important you understand the repercussions. Remember that other Plugins depend on those event 
types. It is *highly recommended* that you extend the appropriate Labrador event that you're replacing. 
This ensures that event listener type declarations still pass and expected functionality is still 
provided.