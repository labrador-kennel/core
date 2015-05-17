# Labrador

[![Build Status](https://travis-ci.org/cspray/labrador.svg?branch=master)](https://travis-ci.org/cspray/labrador.svg?branch=master)
[![License](https://poser.pugx.org/cspray/labrador/license.png)](https://packagist.org/packages/cspray/labrador)



## Dependencies

Labrador has a few dependencies that must be provided.

- PHP 5.6+
- [rdlowrey/Auryn](https://github.com/rdlowrey/Auryn) IoC container to provision dependencies and encourages using dependency injection
- [evenement/evenement](https://github.com/igorw/evenement) Emits events to registered listeners, the primary driving force behind Labrador's functionality.

## Installation

There's 2 supported ways to install Labrador:

1. Composer

We recommend you install Labrador via Composer.
 
`composer require cspray/labrador dev-master`

2. Git

If Composer is too fancy for your blood you can always clone this repo.

`git clone git@github.com/cspray/labrador`

## Hello World

Here are 2 examples of a Hello World application; one using Labrador directly and the other by implementing 
a Plugin to do the work for us.

### Using Labrador Directly

```php
<?php

require_once './vendor/autoload.php';

use Labrador\CoreEngine;
use Labrador\Plugin\PluginManager;
use Auryn\Injector;
use Evenement\EventEmitter;

// it is recommended you install filp/whoops to handle outputting exception messages
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

$injector = new Injector();
$emitter = new EventEmitter();
$pluginManager = new PluginManager($injector, $emitter);
$engine = new CoreEngine($emitter, $pluginManager);

$excHandler = new Run();
$excHandler->pushHandler(new PrettyPageHandler());

// Important that you set a handler for the ExceptionThrowEvent, otherwise exceptions 
// in your app may be silently squashed.
$engine->onExceptionThrown(function(ExceptionThrownEvent $event) use($excHandler) {
    $excHandler->handleException($event->getException());
});

$engine->onAppExecute(function() {
    echo 'Hello World';
});

$engine->run();
```




### As a Plugin

```php
<?php

require_once './vendor/autoload.php';

use Labrador\CoreEngine;
use Labrador\Plugin;
use Auryn\Injector;
use Evenement\EventEmitterInterface;

class HelloWorldPlugin implements Plugin\EventAwarePlugin {

    public function getName() {
        return 'labrador.hello_world';
    }

    public function boot() {
        // our app is too simple to do anything here but yours might not be
    }

    public function registerEventListeners(EventEmitterInterface $emitter) {
        $emitter->on(CoreEngine::APP_EXECUTE_EVENT, function() {
            echo 'Hello world!';
        });
    }

    // Check out the Plugin\ServiceAwarePlugin if you need to provide services
    
}

$emitter = new EventEmitter();
$pluginManager = new Pluing\PluginManager(new Auryn\Provider, $emitter);
$engine = new Engine($eventDispatcher, $pluginManager);

$engine->registerPlugin(new HelloWorldPlugin());
$engine->run();
```

## Installation

We recommend you use Composer to install Labrador.

`require cspray/labrador dev-master`

```php
<?php

$me->assumesCompetentDeveloper();

if (!$you->usingComposer()) {
    $you->downloadLabrador();
    $you->downloadDependencies();
    $you->setupAutoloader();
}
```

### What's up with the name?

Right around the time I started this project my wife and I acquired a new family member; 
Nick, a chocolate Labrador Retriever, came bounding into our lives. I'm horrible at naming 
things and Labrador was an obvious choice at the time. You can think of Labrador the library 
as similar to the dog; friendly, eager to please, and lets you lead the way.
