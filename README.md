# Labrador

[![Build Status](https://travis-ci.org/cspray/labrador.svg?branch=master)](https://travis-ci.org/cspray/labrador.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cspray/labrador/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/cspray/labrador/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
[![License](https://poser.pugx.org/cspray/labrador/license.png)](https://packagist.org/packages/cspray/labrador)

A small, event dispatching library that encourages applications to adhere to the following philosophy:

- Be built of small, composable, reusable libraries
- Be framework agnostic
- Use dependency injection throughyout your software stack
- Be thoroughly and easily unit-tested

Here's the simplest "Hello world" Labrador style. Labrador isn't really meant to develop such a simple, meaningless application but it will give you an idea on how Labrador is used at a basic level.

```php
<?php

require_once './vendor/autoload.php';

use Labrador\Engine;
use Labrador\Plugin\EventAwarePlugin;
use Labrador\Plugin\PluginManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HelloWorldPlugin implements EventAwarePlugin {

    public function getName() {
        return 'labrador.hello_world';
    }

    public function boot() {
        // our app is too simple to do anything here but yours might not be
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher) {
        $eventDispatcher->addListener(Engine::APP_EXECUTE_EVENT, function() {
            echo 'Hello world!';
        });
    }

}

$eventDispatcher = new EventDispatcher();
$pluginManager = new PluginManager(new Auryn\Provider, $eventDispatcher);
$engine = new Engine($eventDispatcher, $pluginManager);

$engine->registerPlugin(new HelloWorldPlugin());
$engine->run();
```

## Dependencies

Labrador has a few dependencies that must be provided.

- PHP 5.5+
- [rdlowrey/Auryn](https://github.com/rdlowrey/Auryn) DI container to provision dependencies and incourage a dependency-injection driven development.
- [symfony/EventDispatcher](https://github.com/symfony/EventDispatcher) Dispatches events to registered listeners, the primary driving force behind Labrador's functionality.

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

Right around the time I started this project my wife and I acquired a new family member; Nick, a chocolate Labrador Retriever, came bounding into our lives. I'm horrible at naming things and Labrador was an obvious choice at the time. You can think of Labrador the library as similar to the dog; friendly, eager to please, and lets you lead the way.
