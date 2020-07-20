<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Amp\Deferred;
use Amp\Delayed;
use Amp\Failure;
use Amp\Loop;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\ApplicationState;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Test\Stub\PluginStub;
use Psr\Log\Test\TestLogger;
use function Amp\call;

/**
 *
 * @package Cspray\Labrador\Test
 * @license See LICENSE in source root
 */
class AbstractApplicationTest extends AsyncTestCase {

    private $pluggable;

    /** @var AbstractApplication */
    private $subject;

    private $logger;

    public function setUp() : void {
        parent::setUp();
        $this->logger = new TestLogger();
        $this->pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $this->subject = $this->getMockForAbstractClass(AbstractApplication::class, [$this->pluggable]);
        $this->subject->setLogger($this->logger);
    }

    public function testRegisterPlugingDelegatedToPluggable() {
        $this->pluggable->expects($this->once())->method('registerPlugin')->with('PluginClass');
        $this->subject->registerPlugin('PluginClass');
    }

    public function testRemovePlugingDelegatedToPluggable() {
        $this->pluggable->expects($this->once())->method('removePlugin')->with('PluginClass');
        $this->subject->removePlugin('PluginClass');
    }

    public function testLoadPluginsDelegatedToPluggable() {
        $promise = new Success();
        $this->pluggable->expects($this->once())->method('loadPlugins')->willReturn($promise);
        $actual = $this->subject->loadPlugins();

        $this->assertSame($promise, $actual);
    }

    public function testGetRegisteredPluginsDelegatedToPluggable() {
        $this->pluggable->expects($this->once())->method('getRegisteredPlugins')->willReturn(['PluginClass']);
        $actual = $this->subject->getRegisteredPlugins();

        $this->assertSame(['PluginClass'], $actual);
    }

    public function testGetLoadedPluginsDelegatedToPluggable() {
        $plugin = new PluginStub();
        $this->pluggable->expects($this->once())->method('getLoadedPlugins')->willReturn([$plugin]);
        $actual = $this->subject->getLoadedPlugins();

        $this->assertSame([$plugin], $actual);
    }

    public function testGetLoadedPluginDelegatedToPluggable() {
        $plugin = new PluginStub();
        $this->pluggable->expects($this->once())
            ->method('getLoadedPlugin')
            ->with(PluginStub::class)
            ->willReturn($plugin);
        $actual = $this->subject->getLoadedPlugin(PluginStub::class);

        $this->assertSame($plugin, $actual);
    }

    public function testHavePluginsLoadedDelegatedToPluggable() {
        $this->pluggable->expects($this->once())->method('havePluginsLoaded')->willReturn(true);
        $actual = $this->subject->havePluginsLoaded();

        $this->assertTrue($actual);
    }

    public function testHasPluginBeenRegisteredDelegatedToPluggable() {
        $this->pluggable->expects($this->once())
            ->method('hasPluginBeenRegistered')
            ->with('PluginClass')
            ->willReturn(true);
        $actual = $this->subject->hasPluginBeenRegistered('PluginClass');

        $this->assertTrue($actual);
    }

    public function testRegisterPluginLoadHandlerDelegatedToPluggable() {
        $handler = function() {
        };
        $this->pluggable->expects($this->once())
            ->method('registerPluginLoadHandler')
            ->with('PluginClass', $handler, 1, 2, 'foo');

        $this->subject->registerPluginLoadHandler('PluginClass', $handler, 1, 2, 'foo');
    }

    public function testRegisterPluginRemoveHandlerDelegatedToPluggable() {
        $handler = function() {
        };
        $this->pluggable->expects($this->once())
            ->method('registerPluginRemoveHandler')
            ->with('PluginClass', $handler, 1, 2, 'foo');

        $this->subject->registerPluginRemoveHandler('PluginClass', $handler, 1, 2, 'foo');
    }

    public function testApplicationStartPromiseResolvesWhenStopCalled() {
        $this->subject->expects($this->once())->method('doStart')->willReturn((new Deferred())->promise());
        $resolved = false;
        $this->subject->start()->onResolve(function() use(&$resolved) {
            $resolved = true;
        });

        $this->assertFalse($resolved);

        yield $this->subject->stop();

        $this->assertTrue($resolved);
    }

    public function testApplicationStartPromiseResolvesWhenDelegateResolves() {
        return call(function() {
            $this->subject->expects($this->once())->method('doStart')->willReturn(new Success());

            yield $this->subject->start();

            // we only get here if the Promise from start() resolves
            $this->assertTrue(true);
        });
    }

    public function testApplicationStateBeforeStartIsStopped() {
        $this->assertSame(ApplicationState::Stopped(), $this->subject->getState());
    }

    public function testApplicationStateRespondsToStartingNaturallyStopping() {
        $this->subject->expects($this->once())->method('doStart')->willReturn(new Success());
        $this->subject->start();

        $this->assertSame(ApplicationState::Started(), $this->subject->getState());

        yield new Delayed(0);

        $this->assertSame(ApplicationState::Stopped(), $this->subject->getState());
    }

    public function testApplicationStateRespondsToStartingExplicitlyStopping() {
        $this->subject->expects($this->once())->method('doStart')->willReturn((new Deferred())->promise());
        $this->subject->start();

        $this->assertSame(ApplicationState::Started(), $this->subject->getState());

        yield $this->subject->stop();

        $this->assertSame(ApplicationState::Stopped(), $this->subject->getState());
    }

    public function testApplicationStateFlipsToCrashedWhenDoStartFails() {
        $exception = new \RuntimeException('Thrown from doStart');
        $this->subject->expects($this->once())->method('doStart')->willReturn(new Failure($exception));

        try {
            yield $this->subject->start();
        } catch (\RuntimeException $runtimeException) {
            $this->assertSame(ApplicationState::Crashed(), $this->subject->getState());
            $this->assertSame($exception, $runtimeException);
        }
    }

    public function testApplicationStartSuccessiveTimesThrowsException() {
        $this->subject->expects($this->once())->method('doStart')->willReturn(new Delayed(0));
        $this->subject->start();

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage(
            'Application must be in a Stopped state to start but it\'s current state is Started'
        );

        $this->subject->start();
    }

    public function testHandleExceptionLogsErrorNoPreviousException() {
        $throwable = new \RuntimeException('Exception message', 99);
        $this->subject->handleException($throwable);

        $expectedRecords = [
            [
                'level' => 'critical',
                'message' => 'Exception message',
                'context' => [
                    'class' => \RuntimeException::class,
                    'file' => __FILE__,
                    'line' => 198,
                    'code' => 99,
                    'stack_trace' => $throwable->getTrace(),
                    'previous' => null
                ]
            ]
        ];

        $this->assertSame($expectedRecords, $this->logger->records);
    }

    public function testHandleExceptionLogsErrorWithPreviousException() {
        $first = new \RuntimeException('First');
        $second = new \RuntimeException('Second', 0, $first);
        $throwable = new \RuntimeException('Exception message', 99, $second);
        $this->subject->handleException($throwable);

        $expectedRecords = [
            [
                'level' => 'critical',
                'message' => 'Exception message',
                'context' => [
                    'class' => \RuntimeException::class,
                    'file' => __FILE__,
                    'line' => 222,
                    'code' => 99,
                    'stack_trace' => $throwable->getTrace(),
                    'previous' => [
                        'class' => \RuntimeException::class,
                        'message' => 'Second',
                        'code' => 0,
                        'file' => __FILE__,
                        'line' => 221,
                        'stack_trace' => $second->getTrace(),
                        'previous' => [
                            'class' => \RuntimeException::class,
                            'message' => 'First',
                            'code' => 0,
                            'file' => __FILE__,
                            'line' => 220,
                            'stack_trace' => $first->getTrace(),
                            'previous' => null
                        ]
                    ]
                ]
            ]
        ];

        $this->assertSame($expectedRecords, $this->logger->records);
    }
}
