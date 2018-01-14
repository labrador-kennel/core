<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\Labrador\Test;

use Amp\Loop;
use Cspray\Labrador\{Engine, PluginManager};
use Cspray\Labrador\Plugin\Plugin;

use Cspray\Labrador\Exception\{
    InvalidArgumentException, NotFoundException, CircularDependencyException, PluginDependencyNotProvidedException
};

use Cspray\Labrador\Test\Stub\{
    BootCalledPlugin,
    CircularDependencyPluginStub,
    EventsRegisteredPlugin,
    FooPluginDependentStub,
    FooPluginStub,
    PluginStub,
    RecursivelyDependentPluginStub,
    RequiresCircularDependentStub,
    RequiresNotPresentPlugin,
    ServicesRegisteredPlugin
};
use Cspray\Labrador\AsyncEvent\{
    Emitter, AmpEmitter as EventEmitter, Event, StandardEvent
};
use Auryn\Injector;
use PHPUnit\Framework\TestCase as UnitTestCase;

class PluginManagerTest extends UnitTestCase {

    private $mockDispatcher;
    private $mockInjector;

    public function setUp() {
        $this->mockDispatcher = $this->createMock(Emitter::class);
        $this->mockInjector = $this->getMockBuilder(Injector::class)->disableOriginalConstructor()->getMock();
    }

    private function getPluginManager() {
        return new PluginManager($this->mockInjector, $this->mockDispatcher);
    }

    private function getPluginBooter(PluginManager $mgr) {
        $r = new \ReflectionClass($mgr);
        $m = $r->getMethod('getBooter');
        $m->setAccessible(true);
        return $m->invoke($mgr);
    }

    private function standardEvent(string $name, $target, array $eventData = []) : Event {
        return new StandardEvent($name, $target, $eventData);
    }

    public function testManagerHasRegisteredPlugin() {
        $plugin = new PluginStub();
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertTrue($manager->hasPlugin(PluginStub::class));
    }

    public function testManagerDoesNotHavePlugin() {
        $manager = $this->getPluginManager();
        $this->assertFalse($manager->hasPlugin(PluginStub::class));
    }

    public function testGettingRegisteredPlugin() {
        $plugin = new PluginStub();
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertSame($plugin, $manager->getPlugin(PluginStub::class));
    }

    public function testGettingUnregisteredPluginThrowsException() {
        $manager = $this->getPluginManager();
        $msg = 'Could not find a registered plugin named "%s"';

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf($msg, PluginStub::class));

        $manager->getPlugin(PluginStub::class);
    }

    public function testRemovingRegisteredPlugin() {
        $plugin = new PluginStub();
        $manager = $this->getPluginManager();
        $manager->registerPlugin($plugin);
        $this->assertTrue($manager->hasPlugin(PluginStub::class));

        $manager->removePlugin(PluginStub::class);
        $this->assertFalse($manager->hasPlugin(PluginStub::class));
    }

    public function testGettingCopyOfPlugins() {
        $manager = $this->getPluginManager();
        $copy = $manager->getPlugins();
        $stub = new PluginStub();
        $copy['stub'] = $stub;

        $this->assertFalse($manager->hasPlugin(PluginStub::class));
    }

    public function correctPluginMethodsCalledProvider() {
        return [
            [new BootCalledPlugin()],
            [new ServicesRegisteredPlugin()],
            [new EventsRegisteredPlugin()]
        ];
    }

    /**
     * @dataProvider correctPluginMethodsCalledProvider
     */
    public function testPluginMethodsCalled(Plugin $plugin) {
        $emitter = new EventEmitter();
        $manager = new PluginManager($this->mockInjector, $emitter);
        $manager->registerPlugin($plugin);

        $engine = $this->getMockBuilder(Engine::class)->disableOriginalConstructor()->getMock();

        Loop::run(function() use($emitter, $engine) {
            $emitter->emit($this->standardEvent(Engine::ENGINE_BOOTUP_EVENT, $engine));
        });

        $this->assertTrue($plugin->wasCalled());
    }

    public function testSingleDependsOnProcessed() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin($plugin = new FooPluginDependentStub($injector));
        $manager->registerPlugin(new FooPluginStub());

        $booter->bootPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    public function testDependsOnLoadedOnce() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin(new FooPluginDependentStub($injector));
        $manager->registerPlugin($plugin = new FooPluginStub());

        $booter->bootPlugins();

        $this->assertSame(1, $plugin->getNumberTimesBootCalled());
    }

    public function testDependentPluginDependsOnDependentPlugin() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin($plugin = new RecursivelyDependentPluginStub($injector));
        $manager->registerPlugin(new FooPluginDependentStub($injector));
        $manager->registerPlugin(new FooPluginStub());

        $booter->bootPlugins();

        $this->assertTrue($plugin->wasDependsOnProvided(), 'Depends on services not provided');
    }

    public function testHandlingCircularDependency() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin(new CircularDependencyPluginStub());
        $manager->registerPlugin(new RequiresCircularDependentStub());

        $exc = CircularDependencyException::class;
        $msg = "A circular dependency was found with Cspray\\Labrador\\Test\\Stub\\RequiresCircularDependentStub requiring Cspray\\Labrador\\Test\\Stub\\CircularDependencyPluginStub.";

        $this->expectException($exc);
        $this->expectExceptionMessage($msg);

        $booter->bootPlugins();
    }

    public function testDependentPluginNotPresentThrowsException() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);
        $booter = $this->getPluginBooter($manager);

        $manager->registerPlugin(new RequiresNotPresentPlugin());

        $exc = PluginDependencyNotProvidedException::class;
        $msg = 'Cspray\\Labrador\\Test\\Stub\\RequiresNotPresentPlugin requires a plugin that is not registered: SomeAwesomePlugin.';

        $this->expectException($exc);
        $this->expectExceptionMessage($msg);

        $booter->bootPlugins();
    }

    public function testLoadPluginIfRegisteredAfterPluginBootEvent() {
        $emitter = new EventEmitter();
        $manager = new PluginManager($this->mockInjector, $emitter);

        Loop::run(function() use($emitter) {
            $emitter->emit($this->standardEvent(Engine::ENGINE_BOOTUP_EVENT, new \stdClass()));
        });

        $plugin = new BootCalledPlugin();

        $manager->registerPlugin($plugin);

        $this->assertTrue($plugin->wasCalled());
    }

    public function testRegisteringSamePluginThrowsException() {
        $injector = new Injector();
        $manager = new PluginManager($injector, $this->mockDispatcher);

        $plugin = new FooPluginStub();
        $manager->registerPlugin($plugin);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A Plugin with name ' . FooPluginStub::class . ' has already been registered and may not be registered again.');

        $manager->registerPlugin($plugin);
    }
}
