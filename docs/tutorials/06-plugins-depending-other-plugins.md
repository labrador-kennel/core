# Plugins: Depending on other Plugins

Sometimes you need to rely on a service provided by another `Plugin` or need to ensure that Plugin has done some other 
thing before your Plugin will work correctly. Implementing the `PluginDependentPlugin` will ensure that any other 
Plugins you depend on will be loaded first.

### Implementing `PluginDependentPlugin`

### Next Steps

{% include core/plugin_next_steps.md hide='depend' %}
