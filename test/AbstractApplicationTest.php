<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Amp\Success;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Test\Stub\PluginStub;
use PHPUnit\Framework\TestCase;

/**
 *
 * @package Cspray\Labrador\Test
 * @license See LICENSE in source root
 */
class AbstractApplicationTest extends TestCase {

    private $pluggable;
    /** @var AbstractApplication */
    private $subject;

    public function setUp() : void {
        $this->pluggable = $this->getMockBuilder(Pluggable::class)->getMock();
        $this->subject = $this->getMockForAbstractClass(AbstractApplication::class, [$this->pluggable]);
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
}
