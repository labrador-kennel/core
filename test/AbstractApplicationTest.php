<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Amp\DeferredFuture;
use Amp\Future;
use Amp\PHPUnit\AsyncTestCase;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\Application;
use Cspray\Labrador\ApplicationState;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Test\Stub\PluginStub;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Revolt\EventLoop;
use function Amp\delay;

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

    private TestHandler $logHandler;

    public function setUp() : void {
        parent::setUp();
        $this->logHandler = new TestHandler();
        $this->logger = new Logger('labrador-core-test', [$this->logHandler]);
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
        $this->pluggable->expects($this->once())->method('loadPlugins');
        $actual = $this->subject->loadPlugins();
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
        $this->subject->expects($this->once())->method('doStart')->willReturn((new DeferredFuture())->getFuture());
        $resolved = false;
        $this->subject->start()->map(function() use(&$resolved) {
            $resolved = true;
        });

        $this->assertFalse($resolved);

        $this->subject->stop()->await();

        $this->assertTrue($resolved);
    }

    public function testApplicationStartPromiseResolvesWhenDelegateResolves() {
        $this->subject->expects($this->once())->method('doStart')->willReturn(Future::complete());

        $this->subject->start()->await();

        // we only get here if the Promise from start() resolves
        $this->assertTrue(true);
    }

    public function testApplicationStateBeforeStartIsStopped() {
        $this->assertSame(ApplicationState::Stopped(), $this->subject->getState());
    }

    public function testApplicationStateRespondsToStartingNaturallyStopping() {
        $this->subject->expects($this->once())->method('doStart')->willReturn(Future::complete());
        $this->subject->start();

        $this->assertSame(ApplicationState::Started(), $this->subject->getState());

        delay(0);

        $this->assertSame(ApplicationState::Stopped(), $this->subject->getState());
    }

    public function testApplicationStateRespondsToStartingExplicitlyStopping() {
        $this->subject->expects($this->once())->method('doStart')->willReturn((new DeferredFuture())->getFuture());
        $this->subject->start();

        $this->assertSame(ApplicationState::Started(), $this->subject->getState());

        $this->subject->stop()->await();

        $this->assertSame(ApplicationState::Stopped(), $this->subject->getState());
    }

    public function testApplicationStateFlipsToCrashedWhenDoStartFails() {
        $exception = new \RuntimeException('Thrown from doStart');
        $this->subject->expects($this->once())->method('doStart')->willReturn(Future::error($exception));

        try {
            $this->subject->start()->await();
        } catch (\RuntimeException $runtimeException) {
            $this->assertSame(ApplicationState::Crashed(), $this->subject->getState());
            $this->assertSame($exception, $runtimeException);
        }
    }

    public function testHandleExceptionLogsErrorWithPreviousException() {
        $throwable = new \RuntimeException('Exception message');

        try {
            $this->subject->handleException($throwable);
        } catch (\RuntimeException $runtimeException) {
        }

        $this->assertTrue($this->logHandler->hasCriticalThatMatches('#Exception message#'));
    }
}
