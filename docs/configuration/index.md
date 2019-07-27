---
layout: docs
---
## Configuration Documentation

While Labrador certainly establishes its own opinions about how to write asynchronous software with PHP we aim to do so 
in a way that it doesn't stomp over _your_ opinions on how to write your Application. We aspire to a Labrador community 
that establishes its own _de facto_ conventions that are defined through explicit configuration.

If your app was generated through the `labrador-app-skeleton` command or you simply want to take advantage of the 
`ConfiguredApplicationInvoker` out of the box you'll need to make sure your Configuration is setup properly. First, we'll 
take a look at each attribute in a Configuration and why we need it. Then we'll take a look at how you can provide the 
configuration in a variety of formats including XML, JSON, and a PHP array.

### Attributes

<dl>
    <dt>logName</dt>
    <dd>
        Type: string<br>
        Defines the name of the log used for your app and is used to identify log lines your app generates.
    </dd>
    <dt>logOutputPath</dt>
    <dd>
        Type: string<br>
        Defines the location log entries will be stored. This path should be capable of creating a valid resource from `fopen`.
    </dd>
    <dt>injectorProviderPath</dt>
    <dd>
        Type: string<br>
        Defines the location that returns a callable that will accept a `Configuration` instance as its only parameter and return an `Auryn\Injector`.
    </dd>
    <dt>plugins</dt>
    <dd>
        Type: string[]<br>
        Defines a set of fully-qualified-names for the Plugins that should be registered to your Application.
    </dd>
</dl>

### Formats

Labrador supports three configuration formats out-of-the-box: XML, JSON, and PHP. The XML and JSON formats are 
straightforward and backed by their own schemas. The PHP format supports returning an array or a Configuration 
instance. Please note that if your PHP configuration returns an array its JSON representation MUST adhere to the 
JSON schema.

#### XML

Schema: <a href="schemas/configuration.schema.xsd">https://labrador-kennel.io/core/schemas/configuration.schema.xsd

```xml
<?xml version="1.0" encoding="UTF-8"?>
<labrador xmlns="https://labrador-kennel.io/core/schemas/configuration.schema.xsd">
  <logging>
    <name>YourApp</name>
    <outputPath>php://stdout</outputPath>
  </logging>
  <injectorProviderPath>your/config/path/injector.php</injectorProviderPath>
  <plugins>
    <plugin>YourApp\CustomPlugin</plugin>
  </plugins>
</labrador>
```

#### JSON

Schema: <a href="schemas/configuration.schema.json">https://labrador-kennel.io/core/schemas/configuration.schema.json</a>

```json
{
  "labrador": {
    "logging": {
      "name": "YourApp",
      "outputPath": "php://stdout"
    },
    "injectorProviderPath": "your/config/path/injector.php",
    "plugins": [
      "YourApp\\CustomPlugin"
    ]
  }
}
```

#### PHP

##### Array

The JSON representation of the returned array MUST adhere to the above JSON schema.

```php
<?php

return [
    'labrador' => [
        'logging' => [
            'name' => 'YourApp',
            'outputPath' => 'php://stdout'    
        ],
        'injectorProviderPath' => 'your/config/path/injector.php',
        'plugins' => [
            'YourApp\\CustomPlugin'    
        ]  
    ]
];
```

##### Configuration Instance

In this example we assume that you have created your own Configuration implementation and would like to use that 
instead of the implementation provided by the ConfigurationFactory. In your Labrador configuration file simply 
return any object that implements the Configuration interface.

```php
<?php

return new YourApp\MyConfiguration();
```

#### Other Formats

There are no plans to natively support any other configuration format in Labrador. With the capabilities of the 
PHP based configuration however it would be a simple task to provide the configuration in your own desired format 
and marshal that into either a PHP array or a new Configuration implementation.

Next you should check out more about your Application instance as it is the heart of your app.

{% include next_previous_article_nav.md
   previous_article_name="Home"
   previous_article_url=""
   next_article_name="Applications"
%}
