<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Plugin;

use Labrador\Stub\NameOnlyPlugin;
use PHPUnit_Framework_TestCase as UnitTestCase;

class PluginCollectionTest extends UnitTestCase {

    public function testAddReturnsCollection() {
        $collection = new PluginCollection();
        $stub = new NameOnlyPlugin('foo');
        $this->assertSame($collection, $collection->add($stub));
    }

    public function testAddPluginHasIt() {
        $collection = new PluginCollection();
        $stub = new NameOnlyPlugin('foobar');
        $collection->add($stub);
        $this->assertTrue($collection->has('foobar'));
    }

    public function testUnaddedPluginDoesNotHaveIt() {
        $collection = new PluginCollection();
        $this->assertFalse($collection->has('foobar'));
    }

    public function testGettingAddedPlugin() {
        $collection = new PluginCollection();
        $stub = new NameOnlyPlugin('stub');
        $collection->add($stub);
        $this->assertSame($stub, $collection->get('stub'));
    }

    public function testGettingUnaddedPluginReturnsNull() {
        $collection = new PluginCollection();
        $this->assertNull($collection->get('stub'));
    }

    public function testEmptyCollectionIsEmpty() {
        $collection = new PluginCollection();
        $this->assertTrue($collection->isEmpty());
    }

    public function testPopulatedCollectionIsNotEmpty() {
        $collection = new PluginCollection();
        $plugin = new NameOnlyPlugin('stub');
        $collection->add($plugin);

        $this->assertFalse($collection->isEmpty());
    }

    public function testGettingEmptyCollectionAsArray() {
        $collection = new PluginCollection();
        $this->assertSame([], $collection->toArray());
    }

    public function testGettingPopulatedCollectionAsArray() {
        $collection = new PluginCollection();
        $foo = new NameOnlyPlugin('foo');
        $bar = new NameOnlyPlugin('bar');
        $baz = new NameOnlyPlugin('baz');

        $collection->add($foo);
        $collection->add($bar);
        $collection->add($baz);

        $this->assertSame([$foo, $bar, $baz], $collection->toArray());
    }

    public function testRemovingPlugin() {
        $collection = new PluginCollection();
        $plugin = new NameOnlyPlugin('stub');
        $collection->add($plugin);

        $this->assertTrue($collection->has('stub'));
        $collection->remove('stub');
        $this->assertFalse($collection->has('stub'));
    }

    public function testMappingWithPluginMethod() {
        $collection = new PluginCollection();
        $foo = new NameOnlyPlugin('foo');
        $bar = new NameOnlyPlugin('bar');
        $baz = new NameOnlyPlugin('baz');

        $collection->add($foo);
        $collection->add($bar);
        $collection->add($baz);

        $this->assertSame(['foo', 'bar', 'baz'], $collection->map('getName'));
    }

    public function testMappingWithCallable() {
        $collection = new PluginCollection();
        $foo = new NameOnlyPlugin('foo');
        $bar = new NameOnlyPlugin('bar');
        $baz = new NameOnlyPlugin('baz');

        $collection->add($foo);
        $collection->add($bar);
        $collection->add($baz);

        $cb = function(Plugin $plugin) {
            return $plugin->getName() . '+cb';
        };

        $this->assertSame(['foo+cb', 'bar+cb', 'baz+cb'], $collection->map($cb));
    }

    public function testCopyReturnsPluginCollection() {
        $collection = new PluginCollection();
        $this->assertInstanceOf(PluginCollection::class, $collection->copy());
    }

    public function testCopyDoesNotReturnItself() {
        $collection = new PluginCollection();
        $this->assertNotSame($collection, $collection->copy());
    }

    public function testCopyingReturnsSamePlugins() {
        $collection = new PluginCollection();
        $foo = new NameOnlyPlugin('foo');
        $bar = new NameOnlyPlugin('bar');
        $baz = new NameOnlyPlugin('baz');

        $collection->add($foo);
        $collection->add($bar);
        $collection->add($baz);

        $this->assertSame($collection->toArray(), $collection->copy()->toArray());
    }

} 
