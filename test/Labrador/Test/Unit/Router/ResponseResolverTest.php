<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit\Router;

use Labrador\Router\Resolver\ResponseResolver;
use PHPUnit_Framework_TestCase as UnitTestCase;
use Symfony\Component\HttpFoundation\Response;

class ResponseResolverTest extends UnitTestCase {

    function handlerReturnsFalseProvider() {
        return [
            ['string'],
            [1],
            [null],
            [1.1],
            [[]],
            [new \stdClass()],
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider handlerReturnsFalseProvider
     */
    function testHandlerNotResponseReturnsFalse($handler) {
        $resolver = new ResponseResolver();
        $this->assertFalse($resolver->resolve($handler));
    }

    function testHandlerResponseReturnsCallback() {
        $resolver = new ResponseResolver();
        $response = new Response();

        $controller = $resolver->resolve($response);
        $this->assertTrue(is_callable($controller));
        $this->assertSame($response, $controller());
    }

} 
