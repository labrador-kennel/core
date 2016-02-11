# Labrador

[![Travis](https://img.shields.io/travis/cspray/labrador.svg?style=flat-square)](https://travis-ci.org/cspray/labrador)
[![GitHub license](https://img.shields.io/github/license/cspray/labrador.svg?style=flat-square)](http://opensource.org/licenses/MIT)
[![GitHub release](https://img.shields.io/github/release/cspray/labrador.svg?style=flat-square)](https://github.com/cspray/labrador/releases/latest)

A minimalist PHP 7.0+ library that provides core "modules" to facilitate creating small-to-medium sized PHP 
applications.

- **Data Structures** Provided through the [Ardent](https://github.com/morrisonlevi/Ardent) library.
- **IoC Container** Provided through the [Auryn](https://github.com/rdlowrey/Auryn) library.
- **Events** Provided through [The PHP League Event](https://github.com/thephpleague/event) library.
- **Plugins** A series of simple to implement interfaces provided by Labrador. Plugins can register services to the IoC container, attach callbacks to events and perform bootup actions.
- **Engines** A service that ties events and plugins together to execute your application's primary logic.

You can checkout a "Hello World" example below to get started quickly.

## Installation

There's 2 supported ways to install Labrador:

1. Composer

We recommend you install Labrador via Composer.
 
`composer require cspray/labrador`

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

use Cspray\Labrador\Engine;
use function Cspray\Labrador\bootstrap;

$injector = bootstrap();

$engine = $injector->make(Engine::class);

$engine->onAppExecute(function() {
    echo 'Hello World';
});

$engine->run();
```

### As a Plugin

```php
<?php

require_once './vendor/autoload.php';

use Cspray\Labrador\CoreEngine;
use Cspray\Labrador\Plugin\EventAwarePlugin;
use League\Event\EmitterInterface;
use function Cspray\Labrador\bootstrap;

class HelloWorldPlugin implements EventAwarePlugin {

    public function getName() {
        return 'labrador.hello_world';
    }

    public function boot() {
        // our app is too simple to do anything here but yours might not be
    }

    public function registerEventListeners(EmitterInterface $emitter) {
        $emitter->addListener(CoreEngine::APP_EXECUTE_EVENT, function() {
            echo 'Hello world!';
        });
    }

    // Check out the Plugin\ServiceAwarePlugin if you need to provide services
    
}

$injector = bootstrap();

$engine = $injector->make(Engine::class);

$engine->registerPlugin(new HelloWorldPlugin());
$engine->run();
```

### What's up with the name?

Right around the time I started this project my wife and I acquired a new family member; 
Nick, a chocolate Labrador Retriever, came bounding into our lives. I'm horrible at naming 
things and Labrador was an obvious choice at the time. You can think of Labrador the library 
as similar to the dog; friendly, eager to please, and lets you lead the way.
