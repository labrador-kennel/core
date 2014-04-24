# Labrador

A microframework wiring together high-quality libraries to route HTTP requests to specific controller objects.

## Goals of the Library

- Be small. The goal isn't to be a full-stack framework. Instead we want to make it easy to include other libraries that are built to facilitate the task you're trying to achieve.
- Be flexible. Through TDD and interface based designs we can allow you to change different components of Labrador to fit your needs.
- Be fast. While Labrador isn't intended to do a lot of things it should be performant in everything it does.
- Be not magical. I don't like magical software; everything that Labrador does should be easy to understand and follow. All requests kick off with init.php and the code there should be where you start to learn about Labrador internals.


## Installation

We recommend you use Composer to install Labrador.

`require cspray/labrador 1.0.*`

```php
<?php

if (!$notUsingComposer) {
    $me->assumesCompetentDeveloper();
    $you->downloadLibrary();
    $you->setupPsr0Autoloader('Labrador', '/your/install/path/src');
}
```

## Libraries Used

As of the time of this writing there are 4 external libraries utilized by Labrador, 1 of which was also designed by myself.

### [`nikic/FastRoute`](https://github.com/nikic/FastRoute)

Handles routing a HTTP request to the appropriate handler for that route. Also, as you can tell by its name and the [detailed writeup](http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html) it is pretty fast.

### [`rdlowrey/Auryn`](https://github.com/rdlowrey/Auryn)

An IoC container that allows Labrador, and you, to wire up an object graph and define dependencies.

### [`symfony/HttpKernel`](https://github.com/symfony/HttpKernel)

Provides an extension point to the greater PHP community and takes advantage of pre-built object oriented APIs abstracting HTTP. It is important to note that Labrador does not utilize the `Symfony\Component\HttpKernel` object and only implements `HttpKernelInterface` via `Labrador\Application`. We do take advantage of some other components provided by this part of Symfony.

### [`cspray/Configlet`](https://github.com/cspray/Configlet)

An object oriented means of writing and working with PHP configuration values.

## Getting Started

Labrador is intended to be easy to get started with and to get out of the way, allowing you to develop cool features in your app. We aren't trying to be a RAD framework, we just want to make it easy to customize Labrador to fit your needs. The library allows you to
