<?php


namespace Labrador\Event;

use Labrador\Stub\EventStub;
use PHPUnit_Framework_TestCase as UnitTestCase;

class HaltableEventEmitterTest extends UnitTestCase {

    public function getTestCallback() {
        $a = new \stdClass();
        $a->cbCalled = false;
        $cb = function() use($a) {
            $a->cbCalled = true;
        };
        return [$cb, $a];
    }

    public function getTestCallbackThatHalts() {
        $a = new \stdClass();
        $a->cbCalled = false;
        $cb = function(Event $event) use($a) {
            $event->stopPropagation();
            $a->cbCalled = true;
        };

        return [$cb, $a];
    }

    public function testEmitterHandlesNoArguments() {
        list($cb, $result) = $this->getTestCallback();

        $emitter = new HaltableEventEmitter();
        $emitter->on('foo', $cb);
        $emitter->emit('foo');

        $this->assertTrue($result->cbCalled);
    }

    public function testEmitterHandlesNonEventArguments() {
        list($cb, $result) = $this->getTestCallback();

        $emitter = new HaltableEventEmitter();
        $emitter->on('foo', $cb);
        $emitter->emit('foo', ['not an object']);

        $this->assertTrue($result->cbCalled);
    }

    public function testHaltingEventPropagation() {
        list($first, $firstCalled) = $this->getTestCallback();
        list($second, $secondCalled) = $this->getTestCallbackThatHalts();
        list($third, $thirdCalled) = $this->getTestCallback();

        $emitter = new HaltableEventEmitter();
        $emitter->on('foo', $first);
        $emitter->on('foo', $second);
        $emitter->on('foo', $third);

        $emitter->emit('foo', [new EventStub()]);

        $this->assertTrue($firstCalled->cbCalled && $secondCalled->cbCalled);
        $this->assertFalse($thirdCalled->cbCalled);
    }

    public function testHandleNonHaltingEvents() {
        list($first, $firstCalled) = $this->getTestCallback();
        list($second, $secondCalled) = $this->getTestCallback();
        list($third, $thirdCalled) = $this->getTestCallback();

        $emitter = new HaltableEventEmitter();
        $emitter->on('foo', $first);
        $emitter->on('foo', $second);
        $emitter->on('foo', $third);
        $emitter->emit('foo', [new EventStub()]);

        $this->assertTrue($firstCalled->cbCalled && $secondCalled->cbCalled && $thirdCalled->cbCalled);
    }

}