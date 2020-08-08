<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Adbar\Dot;
use Cspray\Labrador\DotAccessSettings;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Exceptions;
use PHPUnit\Framework\TestCase;

class DotAccessSettingsTest extends TestCase {

    public function testHasValueNotPresentReturnsFalse() {
        $subject = new DotAccessSettings([]);

        $this->assertFalse($subject->has('foo'));
    }

    public function testHasValuePresentReturnsTrue() {
        $subject = new DotAccessSettings(['foo' => ['bar' => ['baz' => 'qux']]]);

        $this->assertTrue($subject->has('foo.bar.baz'));
    }

    public function testGetValueNotPresentThrowsException() {
        $subject = new DotAccessSettings([]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('The setting "foo" could not be found.');
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_KEY_NOT_FOUND);

        $subject->get('foo');
    }

    public function testGetValueIsPresentReturnsIt() {
        $subject = new DotAccessSettings(['foo' => 'bar']);

        $this->assertSame('bar', $subject->get('foo'));
    }

    public function testGetIterator() {
        $subject = new DotAccessSettings(['foo' => 'bar', 'bar' => 'baz', 'baz' => ['qux' => 1]]);

        $expected = ['foo' => 'bar', 'bar' => 'baz', 'baz' => ['qux' => 1]];
        $this->assertSame($expected, iterator_to_array($subject->getIterator()));
    }
}
