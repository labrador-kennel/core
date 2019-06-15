---
layout: docs
---
## Core Documentation

The documentation for Labrador Kennel Core so that you can become familiar with developing for the internal packages
or creating your own applications on top of Labrador Kennel. This package contains all of the "low-level" concepts
including Plugins, Applications, and Engines.

### Installation

You should install all Labrador packages through Composer.

```bash
composer require cspray/labrador
```

### Quick Start

After installing, the quickest way to get started is to take advantage of the provided script that generates your app
skeleton for you. In a terminal, inside the directory that you installed Labrador run the following command:

```bash
vendor/bin/labrador-app-skeleton YourVendor\\AppName 
```

{% include message.html
   message_type="is-warning"
   title="Labrador App Skeleton"
   body="This tool is still in its early stages and assumes some defaults that will be configurable at a later date"
%}

Upon successful execution that should leave you with a directory structure that looks like the following.

```bash
- resources/
    - config/
        |_injector_provider.php
        |_labrador_configuration.xml
- src/
    |_Application.php
    |_DependencyGraph.php
- test/
    |_ApplicationTest.php
    |_DependencyGraphTest.php
- app
- composer.json
- composer.lock
- README.md
- phpunit.xml.dist
```

The first file you'll want to take a look at is in `src/Application.php`. It should resemble the following:

```php
<?php declare(strict_types=1);

namespace YourVendor\AppName;

use Cspray\Labrador\AbstractApplication;
use Amp\Promise;
use function Amp\call;

/**
 *
 * @package YourVendor\AppName
 * @license See LICENSE in source root 
 */
class Application extends AbstractApplication {
    public function execute() : Promise {
        return call(function() {
             // Execute your Application logic here
        });
    }
}
```

As you can see the `AbstractApplication` takes care of a lot of the requirements for implementing an Application, 
leaving you with just executing your Application's business logic. Remember that you're operating within the context 
of an event loop. Additionally, the Application interface is also a `LoggerAwareInterface` and you have access to a 
PSR-3 compliant `getLogger()` implementation as well.

{% include message.html
   message_type="is-info"
   title="PSR-3 LoggerAwareInterface"
   body="The DependencyGraph object provided by Labrador includes the appropriate definitions so that each object created
   by the Injector that implements the LoggerAwareInterface will have the Logger set to it after object creation. If you 
   do not use Labrador's DependencyGraph object you will be require to ensure this happens in your own code."
 %}

Next, it is important to understand how your dependencies are handled so check out `src/DependencyGraph.php`. It should 
look similar to the following.

```php
<?php declare(strict_types=1);

namespace YourVendor\AppName;

use Auryn\Injector;
use Cspray\Labrador\Application as LabradorApplication;

/**
 *
 * @package MyTest\AppNamespace
 * @license See LICENSE in source root
 */
class DependencyGraph {

    public function wireObjectGraph(Injector $injector = null) : Injector {
        $injector = $injector ?? new Injector();

        $injector->share(Application::class);
        $injector->alias(LabradorApplication::class, Application::class);

        return $injector;
    }

}
```

The out-of-the-box wiring simply ensures that whenever you instantiate a Labrador Application that your Application 
implementation is used and that we only ever create one of them. If your Application grows to need its own dependencies 
or your Plugins require advanced wiring you should evolve this class to adjust the object graph accordingly. Please be 
sure to read up on [Auryn documentation](https://github.com/rdlowrey/auryn) if you have any questions about how to handle 
your dependencies.

Finally, you should review the configuration at `resources/config/labrador_configuration.xml`.

{% include message.html
   message_type="is-info"
   title="Configuration Formats"
   body="Is XML not really your cup of tea? No worries! Labrador also supports JSON and PHP configurations out-of-the-box. 
   Please <a href=\"configuration\">read Configuration documentation</a> for more information on how you can use other formats."
%}


```xml
<?xml version="1.0" encoding="UTF-8" ?>
<labrador xmlns="https://labrador-kennel.io/core/schemas/configuration.schema.xsd">
    <logging>
        <name>YourVendor.AppName</name>
        <outputPath>php://stdout</outputPath>
    </logging>
    <injectorProviderPath>resources/config/injector_provider.php</injectorProviderPath>
    <plugins>
    </plugins>
</labrador>
```

Labrador's configuration is pretty straightforward and for the most part this shouldn't need to be adjusted. However, 
if you'd like auto-configuration and registering of your Plugins you'll need to add `<plugin>FQN\Plugin</plugin>` elements 
as appropriate.

Now you're ready to execute your application!

`./app resources/config/labrador_configuration.php`

You're certainly free to check out the `app` file and `resources/config/injector_provider.php` however these files shouldn't 
generally need to be modified in out-of-the-box operation. If you're already comfortable with Labrador's provided
<a href="configuration">Configuration</a> you should <a href="applications">learn more about the Application</a>.

{% include next_previous_article_nav.md 
   next_article_name="Configuration"
%}
