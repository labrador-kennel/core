# Labrador Core Documentation

This is the documentation for Labrador Core so that you can become familiar with working on 
Labrador internally or building your own applications on top of Labrador. Documentation is 
provided in-app to ease maintenance, encourage the creating of docs, and to ensure each version 
has its own documentation easily accessible.

## Labrador is async by design

It is important to remember that Labrador is asynchronous by design and requires a different 
way of thinking if you've only ever developed synchronous PHP applications. It is highly 
recommended that you take a look through, and understand, [Amp's excellent documentation](https://github.com/amphp/amp/tree/v2.0.0-RC1/docs) 
before continuing with Labrador.

## Labrador and dependency injection

Labrador uses dependency injection throughout its codebase and encourages your application to do 
the same. The complexities behind providing and sharing dependencies is taken care of by the 
[Auryn IoC container](https://github.com/rdlowrey/auryn). Auryn does not work like other PHP 
containers. You should understand the power it provides and how to wire an object graph with it 
before continuing with Labrador.

You should severely restrict the amount of code that requires the Auryn container to be injected 
into it. Labrador is designed so that your services are easily declared in a single place and 
the container should not be necessary unless it is being passed into a Factory who's responsibility 
is creating objects with the container.

Your next step should be to check out [Plugins](./plugins) as they define how your code integrates
with Labrador.