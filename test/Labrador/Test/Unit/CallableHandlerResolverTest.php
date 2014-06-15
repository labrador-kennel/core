<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Labrador\Router\CallableHandlerResolver;
use PHPUnit_Framework_TestCase as UnitTestCase;

class CallableHandlerResolverTest extends UnitTestCase {

    function testHandlerIsCallableReturnsHandler() {
        $resolver = new CallableHandlerResolver();
        $closure = function() {};

        $this->assertSame($closure, $resolver->resolve($closure));
    }

    function testHandlerIsNotCallableReturnsFalse() {
        $resolver = new CallableHandlerResolver();

        $this->assertFalse($resolver->resolve('not_callable#action'));
    }

} 
