<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Test\Unit;

use Auryn\Provider;
use Labrador\Router\ProviderHandlerResolver;
use PHPUnit_Framework_TestCase as UnitTestCase;

class ProviderHandlerResolverTest extends UnitTestCase {

    function testNoHashTagInHandlerThrowsException() {
        $handler = 'something_no_hashtag';
        $provider = new Provider();
        $resolver = new ProviderHandlerResolver($provider);

        $this->setExpectedException(
            'Labrador\\Exception\\InvalidHandlerException',
            'A handler must have 1 hashtag delimiting the controller and method to invoke'
        );
        $resolver->resolve($handler);
    }

    function testNoClassThrowsException() {
        $handler = 'Not_Found_Class#action';
        $provider = new Provider();
        $resolver = new ProviderHandlerResolver($provider);

        $this->setExpectedException(
            'Labrador\\Exception\\InvalidHandlerException',
            'There was an error making the requested handler'
        );
        $resolver->resolve($handler);
    }

    function testNoMethodOnControllerThrowsException() {
        $handler = 'Labrador\\Test\\Stub\\HandlerWithoutMethod#action';
        $provider = new Provider();
        $resolver = new ProviderHandlerResolver($provider);

        $this->setExpectedException(
            'Labrador\\Exception\\InvalidHandlerException',
            'The controller and action specified is not appropriately callable'
        );
        $resolver->resolve($handler);
    }

    function testValidControllerActionResultsInRightCallback() {
        $handler = 'Labrador\\Test\\Stub\\HandlerWithMethod#action';
        $val = new \stdClass();
        $val->action = null;
        $provider = new Provider();
        $provider->define('Labrador\\Test\\Stub\\HandlerWithMethod', [':val' => $val]);
        $resolver = new ProviderHandlerResolver($provider);

        $cb = $resolver->resolve($handler);
        $cb($this->getMock('Symfony\\Component\\HttpFoundation\\Request'));

        $this->assertSame('invoked', $val->action);
    }

}
