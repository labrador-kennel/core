# Labrador

[![Build Status](https://travis-ci.org/cspray/labrador.svg?branch=master)](https://travis-ci.org/cspray/labrador.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cspray/labrador/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
[![License](https://poser.pugx.org/cspray/labrador/license.png)](https://packagist.org/packages/cspray/labrador)

> This library is still in development and the provided APIs are likely to change in the short-term future.

A library to facilitate routing an HTTP request to a specific controller. Primarily Labrador wires together *other* high quality, focused libraries to provide core functionality. Labrador is less a framework and more a routing abstraction and an implementation of Symfony's HttpKernelInterface. We highly recommend that you check out the [online Labrador Guide](http://labrador.cspray.net) for more detailed documentation.

## Dependencies

Labrador has a variety of dependencies that must be provided.

- PHP 5.5+
- [nikic/FastRoute](https://github.com/nikic/FastRoute) Router for mapping an HTTP Request to a handler
- [rdlowrey/Auryn](https://github.com/rdlowrey/Auryn) DI container to automatically provision dependencies
- [symfony/HttpKernel](https://github.com/symfony/HttpFoundation) Provides abstraction over HTTP Request/Response and HttpKernelInterface
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

## Hello Labrador

Let's take a look at a simple "Hello World" Labrador style. This example assumes that you have installed Labrador via Composer.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Labrador\Application;
use Labrador\Router\Router;
use Labrador\FrontControllerBootstrap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$aurynInjector = (new FrontControllerBootstrap())->run();
$app = $aurynInjector->make(Application::class);
$router = $aurynInjector->make(Router::class); // OR $app->getRouter();

$router->get('/', function() { return new Response('Hello Labrador'); });

// OR the following are equivalent with out-of-the-box-behavior
// $router->get('/', new Response('Hello Labrador'));
// $router->get('/', YourObject::class. '#methodToInvoke');

$app->handle(Request::createFromGlobals())->send();
```

Check out more about Labrador at the online [Labrador Guide](http://labrador.cspray.net) that details everything you need to know about the library. You can also install [Labrador Guide](http://github.com/cspray/labrador-guide) as a local install. If you need help getting your server setup please check out the documentation below.

### Server Setup

Labrador works with the Front Controller design pattern that dictates that ALL web requests handled by the application be routed through a single script. By convention this is typically a `/public/index.php` file that includes `/init.php`. So, let's break that down into a minimum directory structure needed:

```
/your-install-dir       # the root directory for your project
    |_public            # web accessible files
        |_index.php     # all web requests should be routed here, should only include /your-install-dir/init.php
    |_init.php          # this is where you would wire up Labrador, the Hello World code would be here
```

The names for the specific directories and files are only what we recommend... you do not have to stick with these names and can use whatever convention is already in place for you or your team.

What this means is you need to configure your web server to route all requests to `./public/index.php` or whatever file you choose to be where all dynamic requests are routed. Below are the web configurations for the servers that Labrador has been tested or used on, Apache and Nginx.


 #### Apache Configuration

 ```
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

#### Nginx Configuration

```
server {

    listen *:80;

    root /var/www/awesome/public;
    server_name     awesome.dev www.awesome.dev;
    index index.php;

    location = / {
        try_files @site @site;
    }

    location / {
        try_files $uri @site;
    }

    location @site {
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    }

}
```

> If you find that your home page on Nginx is downloading instead of properly executing the Labrador front controller **you may need to change your `default_type` to `text/html`!**

Keep in mind that these are example configurations and depending on your server's settings you may need to configure the values or change this configuration entirely. If you find ways these configurations can be improved please submit an issue to this repository's issue tracker.

### What's up with the name?

Right around the time I started this project my wife and I acquired a new family member; Nick, a chocolate Labrador Retriever, came bounding into our lives. I'm horrible at naming things and Labrador was an obvious choice at the time. You can think of Labrador the library as similar to the dog; friendly, eager to please, and lets you lead the way.
