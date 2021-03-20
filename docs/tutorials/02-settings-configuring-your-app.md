# Configuring your app with Settings

Most applications will require some amount of configuration. Whether it be the connection to your database, or the settings 
to interact with a RESTful API you'll soon find yourself in need of a standardized, easy-to-understand way of doing so.
In Labrador this is handled with the `Settings`, `SettingsStorageHandler`, and `SettingsLoader` interfaces.

- The `Settings` interface is what you'll interact with in your `Application` and provides the actual configuration for 
your app. This is a read-only interface that will allow you to determine if a value exists and retrieve the value if it 
  does.
- The `SettingsStorageHandler` is responsible for converting a configuration, normally represented as a file on your 
file-system, into a `Settings` object.
- The `SettingsLoader`  is responsible for interacting with the host system to determine appropriate `SettingsStorageHandler` 
to use and act as the interface your code would call to retrieve the `Settings` object.
  
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

    <p><strong>It is imperative that you do not call the `SettingsLoader` API from within the Event Loop!</strong> If you
    need a `SettingsLoader` that requires async operations you should implement your own interface and adjust the bootstrapping 
    code accordingly.</p>
  </div>
</div>