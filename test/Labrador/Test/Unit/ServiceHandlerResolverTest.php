<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Auryn\Provider;
use Labrador\Router\ServiceHandlerResolver;
use PHPUnit_Framework_TestCase as UnitTestCase;

class ServiceHandlerResolverTest extends UnitTestCase {

    function testNoHashTagInHandlerThrowsException() {
        $handler = 'something_no_hashtag';
        $provider = new Provider();
        $resolver = new ServiceHandlerResolver($provider);

        $this->setExpectedException(
            'Labrador\\Exception\\InvalidHandlerException',
            'The handler, something_no_hashtag, is invalid; all handlers must have 1 hashtag delimiting the controller and action.'
        );
        $resolver->resolve($handler);
    }

    function testNoClassThrowsException() {
        $handler = 'Not_Found_Class#action';
        $provider = new Provider();
        $resolver = new ServiceHandlerResolver($provider);

        $this->setExpectedException(
            'Labrador\\Exception\\InvalidHandlerException',
            'An error was encountered creating the controller for Not_Found_Class#action.'
        );
        $resolver->resolve($handler);
    }

    function testNoMethodOnControllerThrowsException() {
        $handler = 'Labrador\\Test\\Stub\\HandlerWithoutMethod#action';
        $provider = new Provider();
        $resolver = new ServiceHandlerResolver($provider);

        $this->setExpectedException(
            'Labrador\\Exception\\InvalidHandlerException',
            'The controller and action, Labrador\\Test\\Stub\\HandlerWithoutMethod::action, is not callable. Please ensure that a publicly accessible method is available with this name.'
        );
        $resolver->resolve($handler);
    }

    function testValidControllerActionResultsInRightCallback() {
        $handler = 'Labrador\\Test\\Stub\\HandlerWithMethod#action';
        $val = new \stdClass();
        $val->action = null;
        $provider = new Provider();
        $provider->define('Labrador\\Test\\Stub\\HandlerWithMethod', [':val' => $val]);
        $resolver = new ServiceHandlerResolver($provider);

        $cb = $resolver->resolve($handler);
        $cb($this->getMock('Symfony\\Component\\HttpFoundation\\Request'));

        $this->assertSame('invoked', $val->action);
    }

}
