# Labrador Core

<div class="repo-badges">
[![Travis](https://travis-ci.org/labrador-kennel/core.svg?branch=master)](https://travis-ci.org/labrador-kennel/core)
[![GitHub license](https://img.shields.io/github/license/labrador-kennel/core.svg?style=flat-square)](http://opensource.org/licenses/MIT)
[![GitHub release](https://img.shields.io/github/release/labrador-kennel/core.svg?style=flat-square)](https://github.com/cspray/labrador/releases/latest)
</div>

- **IoC Container** Provided through the [Auryn](https://github.com/rdlowrey/Auryn) library.
- **Event** An event library designed to execute asynchronously in an Amp event loop. A part of Labrador, you can check out [its repo](https://github.com/labrador-kennel/async-event) for more information.
- **Plugin** A series of simple to implement interfaces provided by Labrador. Plugins can register services to the IoC container, attach callbacks to events, perform bootup actions, and depend on other Plugins!
- **Application** An interface that you implement that provides the primary integration point for your code and Labrador.
- **Engine** A service that ties Events, Plugins, and your Application to execute your code.

## Installation

We only support installing Labrador via [Composer](https://getcomposer.org)
 
```
composer require cspray/labrador
```

## Documentation

Labrador is thoroughly documented in-repo in the `docs/` directory. Please check this out 
if you'd like to learn more about using Labrador.

## Governance

All Labrador packages adhere to the rules laid out in the [Labrador Governance repo](https://github.com/labrador-kennel/governance)

> Organization logo made by [Freepik](http://www.freepik.com) from [www.flaticon.com](http://www.flaticon.com) is licensed by [CC 3.0 BY](http://creativecommons.org/licenses/by/3.0/)
