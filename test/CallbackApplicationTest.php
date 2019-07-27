<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Amp\Delayed;
use Amp\PHPUnit\AsyncTestCase;
use Cspray\Labrador\CallbackApplication;
use Cspray\Labrador\Plugin\Pluggable;

/**
 *
 * @package Cspray\Labrador\Test
 * @license See LICENSE in source root
 */
class CallbackApplicationTest extends AsyncTestCase {

    public function testCallbackApplicationExecutesCallback() {
        $pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $counter = 0;
        $subject = new CallbackApplication(
            $pluggable,
            function() use(&$counter) {
                yield new Delayed(1);
                $counter++;
                yield new Delayed(1);
                $counter++;
                yield new Delayed(1);
                $counter++;
            }
        );

        yield $subject->execute();

        $this->assertSame(3, $counter);
    }

    public function testCallbackApplicationPassesThrownExceptionToExceptionHandler() {
        $pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $exception = null;
        $exceptionToThrow = new \RuntimeException();
        $subject = new CallbackApplication(
            $pluggable,
            function() {
            },
            function(\Throwable $throwable) use(&$exception) {
                $exception = $throwable;
            }
        );

        $subject->exceptionHandler($exceptionToThrow);

        $this->assertSame($exception, $exceptionToThrow);
    }

    public function testCallbackApplicationHandlesExceptionHandlerNotPresent() {
        $pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $subject = new CallbackApplication(
            $pluggable,
            function() {
            }
        );

        $subject->exceptionHandler(new \RuntimeException());

        $this->assertTrue(true, 'Expected to not throw an error so if we get here everything is good');
    }
}
