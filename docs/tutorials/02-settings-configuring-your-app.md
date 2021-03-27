# Configuring your app with Settings

Most applications will require some amount of configuration. Whether it be the connection to your database, or the settings 
to interact with a RESTful API you'll soon find yourself in need of a standardized, easy-to-understand way of configuring 
these portions of your app. In Labrador, we solve this problem with "settings". "Settings", the concept, is a 
**read-only, key-value hierarchy that can be accessed in-code using dot access to traverse through the hierarchy**. In-code 
this is handled by the `Settings`, `SettingsLoader`, and `SettingsStorageHandler` interfaces.  

- The `Settings` interface is what you'll interact with in your `Application` and provides the actual configuration for 
your app. This is a read-only interface that will allow you to determine if a value exists and retrieve the value if it does.
- The `SettingsLoader`  is the API you'll interact with to generate a `Settings` instance. It delegates the low level work 
of interacting with the storage system holding your settings to a `SettingsStorageHandler`.
- The `SettingsStorageHandler` is responsible for dealing with the low-level code required to interact with a storage 
system, normally a local filesystem, to gather and marshal the structure into a PHP array.
  
<div class="message is-danger">
  <div class="message-header">
    <p>Synchronous warning! Please read!</p>
  </div>
  <div class="message-body">
    <p>Settings are one of the pieces of Labrador that out-of-the-box is <strong>NOT asynchronous</strong>! We anticipate 
    that there might be a situation where Settings are needed to be known before an Event Loop has started running. 
    Additionally, we like to use a "fail fast" approach to critical components. If your <code>Settings</code> cannot be 
    loaded it likely represents a problem further along in your app execution where it'll be more problematic. So, you'll 
    find that <code>SettingsLoader</code> has a synchronous API and makes use of synchronous code.</p>

    <p><strong>It is imperative that you do not call the <code>SettingsLoader</code> API from within the Event Loop!</strong> If you
    need a <code>SettingsLoader</code> that requires async operations you should implement your own interface and adjust the bootstrapping 
    code accordingly.</p>
  </div>
</div>

## Settings files

Out-of-the-box Labrador supports configuring your settings in a file on the filesystem storing your application. You can 
create either a PHP or JSON file. We'll show the same configuration in both formats. If neither of these formats are 
appropriate for you it's possible to implement your own `SettingsStorageHander` that supports the format you need.

Our example will showcase all the functionality available when configuring your settings. This includes being able to 
override specific values based on your environment and retrieving values from environment variables for your configuration.
We'll show retrieving common database parameters that are configured in environment variables except for your dev 
environment where the values are simply hardcoded in the configuration.

### Environment configuration overloading



### PHP format

If your configuration does not need to be cross-platform compatible the PHP configuration allows the greatest flexibility. 
Define a file that ends in `php` and return an `array` filled with your values. In our example we'll create 2 files:

- Main settings file created at `/resources/config/settings.php`
- Environment specific file created at `/resources/config/environments/development.php`


```
<?php

// /resources/config/settings.php

return [
    'database' => [
        'adapter' => 'postgres',
        'host' => '!env(DB_HOST)',
        'port' => '!env(DB_PORT)',
        'user' => '!env(DB_USER)',
        'password' => '!env(DB_PASSWORD)'
    ]
];
```

```
<?php

// /resources/config/environments/development.php

return [
    'database' => [
        'host' => 'localhost',
        'port' => 5432,
        'user' => 'postgres',
        'password' => ''
    ]
];
```
