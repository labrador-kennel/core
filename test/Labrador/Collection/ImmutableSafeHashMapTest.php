<?php

namespace Labrador\Collection;

use Labrador\Exception\UnsupportedOperationException;
use PHPUnit_Framework_TestCase as UnitTestCase;

class ImmutableSafeHashMapTest extends UnitTestCase {

    public function testProvidingInitialValues() {
        $map = new ImmutableSafeHashMap(['foo' => 'bar']);

        $this->assertSame('bar', $map['foo']);
    }

    public function settingDataProvider() {
        return [
            ['offsetSet', 'offsetGet'],
            ['set', 'get']
        ];
    }

    /**
     * @dataProvider settingDataProvider
     */
    public function testSettingValueAfterCreationThrowsException($setMethod, $getMethod) {
        $map = new ImmutableSafeHashMap(['foo' => 'bar']);
        $msg = null;
        try {
            $map->$setMethod('foo', 'something');
        } catch (UnsupportedOperationException $exc) {
            $msg = $exc->getMessage();
        }

        $expectedMsg = 'You may not alter the attributes of a ' . ImmutableSafeHashMap::class . ' after instance creation.';
        $this->assertSame($expectedMsg, $msg);
        $this->assertSame('bar', $map->$getMethod('foo'));
    }

    public function unsettingDataProvider() {
        return [
            ['remove'],
            ['offsetUnset']
        ];
    }

    /**
     * @dataProvider unsettingDataProvider
     */
    public function testUnsettingValueAfterCreationgThrowsException($removeMethod) {
        $map = new ImmutableSafeHashMap(['foo' => 'bar']);
        $msg = null;
        try {
            $map->$removeMethod('foo');
        } catch (UnsupportedOperationException $exc) {
            $msg = $exc->getMessage();
        }

        $expectedMsg = 'You may not destroy the attributes of a ' . ImmutableSafeHashMap::class . ' after instance creation.';
        $this->assertSame($expectedMsg, $msg);
        $this->assertSame('bar', $map['foo']);
    }

    public function testProvidingOwnHashingFunction() {
        $hashCalled = false;
        $hashingFunc = function($val) use(&$hashCalled) {
            $hashCalled = true;
            return $val;
        };
        $map = new ImmutableSafeHashMap(['foo' => 'bar'], $hashingFunc);

        $this->assertTrue($hashCalled);
        $this->assertSame('bar', $map['foo']);
    }

}