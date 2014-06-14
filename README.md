# Labrador

[![Build Status](https://travis-ci.org/cspray/labrador.svg?branch=master)](https://travis-ci.org/cspray/labrador.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cspray/labrador/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/cspray/labrador/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
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
- [zendframework/Component_ZendEscaper](https://github.com/zendframework/Component_ZendEscaper) Escaping library for simple, out-of-the-box template rendering

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

Getting started with Labrador is really simple whether you clone the repo directly or install via Composer. First we're gonna talk about getting the required directory structure setup after a Composer installation. If you are cloning the repo directly you can skip to the [server setup]() section as the out-of-the-box directory structure is a part of the repo.

### Directory Setup

One drawback to installing via Composer is that all of the yummy goodness Labrador provides out-of-the-box is buried deep inside a `/vendor` directory. After installing via Composer we need to do a little bit more work to get the required directory structure up and running. Labrador provides a shell script to create the appropriate directory structure and copy over the appropriate files from Labrador's root directory. If you wanna take advantage of this just execute the following commands in your terminal. We assume you're executing this command from the directory where you installed your project's dependencies with Composer.

```plain
./vendor/bin/labrador-skeleton
```

This command will copy over the `/public/*`, '/config/*' and `/init.php` files from Labrador's root directory structure into the directory that the command was executed. This is a safe command; if the file already exists the copy or creation will be aborted and your existing code stays intact.

If you don't want to use the automated method, and really why wouldn't you?, you'll need to manually setup the following directory structure yourself.


```plain
/config
    |_application.php       # where you configure Labrador and your app. see Labrador\ConfigDirective for available configuration keys
    |_bootstrap.php         # where you execute any scripts that should be required before the Labrador\Application handles the Request
/public
    |_/css
        |_labrador_guide/
            |_main.css
        |_normalize.css
        |_prism.css
    |_/img
    |_/js
        |_prism.js
        |_zepto.min.js
    |_index.php     # should only require ../init.php
/vendor
    |_ ...
/init.php           # is where you wire up your application
```

Note that you don't need to setup your directory structure this way and there's a variety of ways to architect the physical file system; Labrador doesn't lock you into any one system and it is very easy to modify. However, to get out-of-the-box behavior up and running it is recommended that this is the directory structure you start off with. As your knowledge of Labrador increases it will be easy to change the physical directory structure when the need arises.

### Server Setup

Labrador has been developed on a VM running Apache. In theory there's nothing that would require you to use Apache and any web server capable of running PHP 5.5 should suffice. But for now the only configurations that we provie examples for is Apache because we haven't confirmed other server's configurations work.

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

## Application Setup

At this point you're ready to start developing your application. So, let's take a look at setting up your app's routes and middleware.


