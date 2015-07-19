# Labrador

[![Build Status](https://travis-ci.org/cspray/labrador.svg?branch=master)](https://travis-ci.org/cspray/labrador)
[![License](https://poser.pugx.org/cspray/labrador/license.png)](https://packagist.org/packages/cspray/labrador)

A minimalist PHP 7.0+ library that provides core "modules" to facilitate creating small-to-medium sized PHP 
applications.

- **Data Structures** Provided through the [Ardent](https://github.com/morrisonlevi/Ardent) library.
- **IoC Container** Provided through the [Auryn](https://github.com/rdlowrey/Auryn) library.
- **Events** Provided through the [Evenement](https://github.com/igorw/evenement) library.
- **Plugins** A series of simple to implement interfaces provided by Labrador.
- **Engines** A service that ties events and plugins together to execute your application's primary logic.

You can checkout a "Hello World" example below to get started quickly. If you'd like more detailed
 information you can check out the [wiki](https://github.com/cspray/labrador/wiki).

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
a Plugin to do the work for us. Clearly Labrador is overkill for something this simple but it explains how you 
could use the library.

### Using Labrador Directly

> If you've already installed Labrador you can execute this example by running `php app.php` from the install directory.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Labrador\Services;
use Labrador\CoreEngine;

$injector = (new Services())->createInjector();
$engine = $injector->make(CoreEngine::class);

$engine->onAppExecute(function() {
    echo 'Hello World';
});

$engine->run();
```

### As a Plugin

```php
<?php

require_once './vendor/autoload.php';

use Labrador\Services;
use Labrador\CoreEngine;
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

$injector = (new Services())->createInjector();
$engine = $injector->make(CoreEngine::class);

$engine->registerPlugin(new HelloWorldPlugin());
$engine->run();
```

### What's up with the name?

Right around the time I started this project my wife and I acquired a new family member; 
Nick, a chocolate Labrador Retriever, came bounding into our lives. I'm horrible at naming 
things and Labrador was an obvious choice at the time. You can think of Labrador the library 
as similar to the dog; friendly, eager to please, and lets you lead the way.
