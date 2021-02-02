<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\SettingsLoader;

use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\DotAccessSettings;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\SettingsLoader\EnvironmentVariableValueSettingsLoader;
use Cspray\Labrador\SettingsLoader\SettingsLoader;
use Cspray\Labrador\StandardEnvironment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EnvironmentVariableValueSettingsLoaderTest extends TestCase {

    public function testDoesNotReplaceValuesThatAreNotEnvCalls() {
        $settings = new DotAccessSettings(['foo' => 'bar']);
        $environment = new StandardEnvironment(EnvironmentType::Development());
        /** @var SettingsLoader|MockObject $settingsLoader */
        $settingsLoader = $this->getMockBuilder(SettingsLoader::class)->getMock();
        $settingsLoader->expects($this->once())
            ->method('loadSettings')
            ->with($environment)
            ->willReturn($settings);
        $subject = new EnvironmentVariableValueSettingsLoader($settingsLoader);

        $actual = $subject->loadSettings($environment);

        $this->assertSame('bar', $actual->get('foo'));
    }

    public function testReplacingEnvironmentVariableValues() {
        $settings = new DotAccessSettings(['foo' => '!env(FOO_BAR)']);
        $environment = new StandardEnvironment(EnvironmentType::Development(), ['FOO_BAR' => 'baz']);
        /** @var SettingsLoader|MockObject $settingsLoader */
        $settingsLoader = $this->getMockBuilder(SettingsLoader::class)->getMock();
        $settingsLoader->expects($this->once())
            ->method('loadSettings')
            ->with($environment)
            ->willReturn($settings);
        $subject = new EnvironmentVariableValueSettingsLoader($settingsLoader);

        $actual = $subject->loadSettings($environment);

        $this->assertSame('baz', $actual->get('foo'));
    }

    public function testReplaceRecursiveSettings() {
        $settings = new DotAccessSettings(['foo' => ['foo_bar' => '!env(FOO_BAR)']]);
        $environment = new StandardEnvironment(EnvironmentType::Development(), ['FOO_BAR' => 'yea']);
        /** @var SettingsLoader|MockObject $settingsLoader */
        $settingsLoader = $this->getMockBuilder(SettingsLoader::class)->getMock();
        $settingsLoader->expects($this->once())
            ->method('loadSettings')
            ->with($environment)
            ->willReturn($settings);
        $subject = new EnvironmentVariableValueSettingsLoader($settingsLoader);

        $actual = $subject->loadSettings($environment);

        $this->assertSame('yea', $actual->get('foo.foo_bar'));
    }

    public function testReplaceNullValueThrowsException() {
        $settings = new DotAccessSettings(['foo' => ['foo_bar' => '!env(FOO_BAR)']]);
        $environment = new StandardEnvironment(EnvironmentType::Development());
        /** @var SettingsLoader|MockObject $settingsLoader */
        $settingsLoader = $this->getMockBuilder(SettingsLoader::class)->getMock();
        $settingsLoader->expects($this->once())
            ->method('loadSettings')
            ->with($environment)
            ->willReturn($settings);
        $subject = new EnvironmentVariableValueSettingsLoader($settingsLoader);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Expected environment variable "FOO_BAR" to have a value but it was null.');
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_ENV_VAR_OVERRIDE_NOT_FOUND);

        $subject->loadSettings($environment);
    }
}
