# Labrador

A microframework wiring together high-quality libraries to route HTTP requests to specific controller objects.

## Installation

We recommend you use Composer to install Labrador.

`require cspray/labrador 1.0.*`

```php
<?php

if (!$usingComposer) {
    $me->assumesCompetentDeveloper();
    $you->downloadLibrary();
    $you->setupPsr0Autoloader('Labrador', '/your/install/path/src');
}
```

