# Labrador

[![Build Status](https://travis-ci.org/cspray/labrador.svg?branch=master)](https://travis-ci.org/cspray/labrador.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cspray/labrador/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
[![License](https://poser.pugx.org/cspray/labrador/license.png)](https://packagist.org/packages/cspray/labrador)

> This library is still in development and the provided APIs are likely to change in the short-term future.

A microframework wiring together high-quality libraries to route HTTP requests to specific controller objects.

## Dependencies

Labrador has a variety of dependencies that must be provided.

- PHP 5.5+
- [nikic/FastRoute](https://github.com/nikic/FastRoute) Router for mapping an HTTP Request to a handler
- [rdlowrey/Auryn](https://github.com/rdlowrey/Auryn) DI container to automatically provision dependencies
- [symfony/HttpFoundation](https://github.com/symfony/HttpFoundation) Provides abstraction over HTTP Request/Response and HttpKernelInterface
- [cspray/Configlet](https://github.com/cspray/Configlet) Object Oriented library for storing PHP configurations

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

## Getting Started

Using Labrador is intended to be easy and straight forward. The User Guide below assumes you have setup your web server to send all non-static requests to be routed to `/public/index.php`. If you need help on setting this up please check out [Server Setup](#server-setup) documentation.

### Quick Start



### Manual Setup

 We're also going to assume that you have done a manual setup and need to setup `/init.php` from a clean slate. If you copied over Labrador's default files your init.php will look slightly different as various pieces are split into `/config` files.

```php
<?php

use Labrador\Application;
use Labrador\ConfigDirective;
use Labrador\Bootstrap\FrontControllerBootstrap;
use Labrador\Events\ApplicationHandleEvent;
use Labrador\Events\ExceptionThrownEvent;
use Auryn\Injector;
use Configlet\Config;

require_once __DIR__ . '/vendor/autoload.php';

// Typically this function would be returned from /config/application.php or some
// other external source.
$appConfig = function(Config $config) {

    // All of these configuration values are optional

    /**
     * The environment that the current application is running in.
     *
     * This is a completely arbitrary string and only holds meaning to your application.
     */
    $config[ConfigDirective::ENVIRONMENT] = 'development';

    /**
     * The root directory for the application
     */
    $config[ConfigDirective::ROOT_DIR] = __DIR__;

    /**
     * A callback accepting a Auryn\Provider as the first argument and a Configlet\Config
     * as the second argument.
     *
     * It should perform actions that are needed at time of request startup including
     * wire up dependencies, set configuration values, and other tasks your application
     * might need to carry out before Labrador actually takes over handling the Request.
     *
     * We recommend you return this from a /config/bootstrap.php or in some other way
     * provide a function from an external source.
     */
    $config[ConfigDirective::BOOTSTRAP_CALLBACK] = function(Injector $injector, Config $config) {
        // do your application bootup stuff here if needed
    };

};

/** @var \Auryn\Injector $injector */
/** @var \Labrador\Application $app */
$injector = (new FrontControllerBootstrap($appConfig))->run();
$app = $injector->make(Application::class);

// perform some action when Application::handle is invoked
$app->onHandle(function(ApplicationHandleEvent $event) {
    // You can set a response to $event->setResponse() to short-circuit processing and return early
    // Your Application::onFinished middleware will still be executed if you short-circuit
});

// perform some action when Application catches an exception
// if you pass Application::THROW_EXCEPTIONS this event will not be triggered
$app->onException(function(ExceptionThrownEvent $event) {

});

// Check out Labrador\Events for more information about hooking into Labrador\Application processing.

$request = Request::createFromGlobals();
$app->handle($request)->send();
```

### Directory Structure

#### Bare Bones

This is the **absolute bare minimum you'll need** to get Labrador properly executing your requests. This will not setup the LabradorGuide and requires you to properly wire up the Labrador\Application. Take a look at the repository's `/init.php` for an example of a working implementation.

```plain
/public             # your webroot, all publicly accessible files should be stored here (i.e. css, js, images)
    |_index.php     # should only require ../init.php
/init.php           # is where you wire up your application
```

#### Advanced

Labrador provides a shell script to automatically setup the appropriate directory structure and wiring that you see in this repo.

```plain
./vendor/bin/labrador-skeleton
```

After this command the directory structure will look similar to the following

```plain
/config
    |_application.php
    |_bootstrap.php
/public
    |_/css
        |_normalize.css
        |_prism.css
    |_/js
        |_prism.js
        |_zepto.min.js
    |_index.php
init.php
```

This command will copy over the `/public/*`, `/config/*` and `/init.php` files from Labrador's root directory structure into the directory that the command was executed.

### Server Setup

Labrador has been developed on a VM running Apache. In theory there's nothing that would require you to use Apache and any web server capable of running PHP 5.5 should suffice. But for now the only configurations that we provide examples for is Apache because we haven't confirmed other server's configurations work.

## Apache Configuration

Below is a slightly modified server configuration for the Apache server that we use in development.

```plain
<VirtualHost *:80>
    ServerName awesome.dev
    ServerAlias www.awesome.dev
    ServerSignature Off

    DocumentRoot "/public"
    FallbackResource /index.php
    DirectoryIndex index.php

    <Directory "/public">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride None
    </Directory>
</VirtualHost>
```

Load this into the appropriate `httpd.conf` file for your server and restart it.

