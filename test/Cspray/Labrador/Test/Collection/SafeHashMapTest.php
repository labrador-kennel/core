<?php

namespace Cspray\Labrador\Test\Collection;

use Cspray\Labrador\Collection\SafeHashMap;
use PHPUnit_Framework_TestCase as UnitTestCase;

class SafeHashMapTest extends UnitTestCase {

    public function testArrayAccessWithKeyNotPresentReturnsNull() {
        $map = new SafeHashMap();

        $this->assertNull($map['not_present']);
    }

    public function testArrayAccessWithKeyPresentReturnsValue() {
        $map = new SafeHashMap();
        $map['foo'] = 'bar';

        $this->assertSame('bar', $map['foo']);
    }

    public function testMapAccessWithKeyNotPresentReturnsNull() {
        $map = new SafeHashMap();

        $this->assertNull($map['not_present']);
    }

    public function testMapAccessWithKeyPresentReturnsValue() {
        $map = new SafeHashMap();
        $map['foo'] = 'bar';

        $this->assertSame('bar', $map['foo']);
    }

}