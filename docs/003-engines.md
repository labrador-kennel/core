# Engines

With Events and Plugins providing the bulk of Labrador's functionality the Engine is the piece that 
brings everything together. Engines are also `Cspray\Labrador\Plugin\Pluggable` implementations and 
they should trigger, at minimum, the Events detailed in the docs. We provide a `Cspray\Labrador\CoreEngine` 
implementation that does all these things.

Ultimately there isn't a lot to go over here as all of the functionality is provided with events and plugins.