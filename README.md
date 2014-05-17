# Labrador

[![Build Status](https://travis-ci.org/cspray/labrador.svg?branch=master)](https://travis-ci.org/cspray/labrador.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cspray/labrador/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/cspray/labrador/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/cspray/labrador/?branch=master)
[![License](https://poser.pugx.org/cspray/labrador/license.png)](https://packagist.org/packages/cspray/labrador)

A microframework wiring together high-quality libraries to route HTTP requests to specific controller objects.

## Installation

We recommend you use Composer to install Labrador.

`require cspray/labrador ~1.0`

```php
<?php

if (!$usingComposer) {
    $me->assumesCompetentDeveloper();
    $you->downloadLibrary();
    $you->setupPsr0Autoloader('Labrador', '/your/install/path/src');
}
```

## Quick Setup

I want Labrador to be really easy for you to get started with.
