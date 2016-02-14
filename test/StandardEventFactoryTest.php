<?php

declare(strict_types = 1);

/**
 * @license See LICENSE file in project root
 */

namespace Cspray\Labrador\Test;

use Cspray\Labrador\Engine;
use Cspray\Labrador\Event\StandardEventFactory;
use Cspray\Labrador\Exception\InvalidTypeException;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Test\Stub\FooEventStub;
use League\Event\Event;
use League\Event\EventInterface;
use PHPUnit_Framework_TestCase as UnitTestCase;

class StandardEventFactoryTest extends UnitTestCase {

    public function testMakingCustomEvent() {
        $factory = new StandardEventFactory();
        $factory->register('foo.event', function() {
            return new FooEventStub();
        });

        $this->assertInstanceOf(FooEventStub::class, $factory->create('foo.event'));
    }

    public function testEventMustBeInstanceOfLeagueEventInterface() {
        $factory = new StandardEventFactory();
        $factory->register('bar.event', function() {
            return 'not an EventInterface';
        });

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Factory functions MUST return an instance of ' . EventInterface::class . ' but "bar.event" returned "string".');

        $factory->create('bar.event');
    }

    public function testEventNameMustMatch() {
        $factory = new StandardEventFactory();
        $factory->register('bar.event', function() {
            return new Event('not.bar.event');
        });

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Factory functions MUST return an instance of ' . EventInterface::class . ' with the same name as "bar.event"');

        $factory->create('bar.event');
    }

    public function testCannotRegisterCoreEvents() {
        $factory = new StandardEventFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You may not register a core event.');

        $factory->register(Engine::APP_EXECUTE_EVENT, function() {});
    }

}