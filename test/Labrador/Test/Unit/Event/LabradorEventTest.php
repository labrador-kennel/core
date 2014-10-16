<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit\Event;

use Labrador\Test\Stub\StubEvent;
use PHPUnit_Framework_TestCase as UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LabradorEventTest extends UnitTestCase {

    function testGettingMasterRequest() {
        $requestStack = new RequestStack();
        $request = Request::create('http://labrador.dev');
        $requestStack->push($request);
        $event = new StubEvent($requestStack);
        $this->assertSame($request, $event->getMasterRequest());
    }

    function testGettingCurrentRequest() {
        $requestStack = new RequestStack();
        $master = Request::create('http://labrador.dev');
        $current = Request::create('http://something.dev');
        $requestStack->push($master);
        $requestStack->push($current);
        $event = new StubEvent($requestStack);
        $this->assertSame($current, $event->getCurrentRequest());
    }

    function testIsMasterRequestTrue() {
        $requestStack = new RequestStack();
        $request = Request::create('http://labrador.dev');
        $requestStack->push($request);
        $event = new StubEvent($requestStack);
        $this->assertTrue($event->isMasterRequest());
    }

    function testIsMasterRequestFalse() {
        $requestStack = new RequestStack();
        $master = Request::create('http://labrador.dev');
        $current = Request::create('http://something.dev');
        $requestStack->push($master);
        $requestStack->push($current);
        $event = new StubEvent($requestStack);
        $this->assertFalse($event->isMasterRequest());
    }


} 
