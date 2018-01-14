# Labrador Core

[![Travis](https://travis-ci.org/labrador-kennel/core.svg?branch=master)](https://travis-ci.org/labrador-kennel/core)
[![GitHub license](https://img.shields.io/github/license/labrador-kennel/core.svg?style=flat-square)](http://opensource.org/licenses/MIT)
[![GitHub release](https://img.shields.io/github/release/labrador-kennel/core.svg?style=flat-square)](https://github.com/cspray/labrador/releases/latest)
[![Dependency Status](https://www.versioneye.com/user/projects/56ee922735630e0029dafb5f/badge.svg?style=flat)](https://www.versioneye.com/user/projects/56ee922735630e0029dafb5f)

A minimalist PHP 7.1+ library that provides core "modules" to facilitate creating small-to-medium sized asynchronous
applications intended to run within an Amp event loop.

- **IoC Container** Provided through the [Auryn](https://github.com/rdlowrey/Auryn) library.
- **Event** An event library designed to execute asynchronously in an Amp event loop. A part of Labrador, you can check out [its repo](https://github.com/labrador-kennel/async-event) for more information.
- **Plugin** A series of simple to implement interfaces provided by Labrador. Plugins can register services to the IoC container, attach callbacks to events, perform bootup actions, and depend on other Plugins!
- **Application** An interface that you implement that provides the primary integration point for your code and Labrador.
- **Engine** A service that ties Events, Plugins, and your Application to execute your code.

For more information please check out the docs/.

## Installation

We only support installing Labrador via [Composer](https://getcomposer.org)
 
```
composer require cspray/labrador
```

## Documentation

Labrador is thoroughly documented in-repo in the `docs/` directory. Please check this out 
if you'd like to learn more about using Labrador.

### What's up with the name?

Right around the time I started this project my wife and I acquired a new family member; 
Nick, a chocolate Labrador Retriever, came bounding into our lives. I'm horrible at naming 
things and Labrador was an obvious choice at the time. You can think of Labrador the library 
as similar to the dog; friendly, eager to please, and lets you lead the way.

> Organization logo made by [Freepik](http://www.freepik.com) from [www.flaticon.com](http://www.flaticon.com) is licensed by [CC 3.0 BY](http://creativecommons.org/licenses/by/3.0/)
