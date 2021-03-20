<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\SettingsLoader;

use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\Environment;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\SettingsLoader\DefaultsWithEnvironmentOverrideSettingsLoader;
use Cspray\Labrador\SettingsStorageHandler\ChainedSettingsStorageHandler;
use Cspray\Labrador\SettingsStorageHandler\JsonFileSystemSettingsStorageHandler;
use Cspray\Labrador\SettingsStorageHandler\PhpFileSystemSettingsStorageHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultsWithEnvironmentOverrideSettingsLoaderTest extends TestCase {

    private $subject;

    private $settingsPath;
    private $environmentConfigDir;

    public function setUp() : void {
        $fileHandler = new ChainedSettingsStorageHandler(new PhpFileSystemSettingsStorageHandler(), new JsonFileSystemSettingsStorageHandler());
        $this->settingsPath = dirname(__DIR__) . '/resources/config/settings.json';
        $this->environmentConfigDir = dirname(__DIR__) . '/resources/config/environment';
        $this->subject = new DefaultsWithEnvironmentOverrideSettingsLoader($fileHandler, $this->settingsPath, $this->environmentConfigDir);
    }

    public function testNoEnvironmentOverrideReturnsSettingsUnchanged() {
        /** @var Environment|MockObject $environment */
        $environment = $this->getMockBuilder(Environment::class)->getMock();
        $environment->expects($this->once())
            ->method('getType')
            ->willReturn(EnvironmentType::Production());
        $settings = $this->subject->loadSettings($environment);

        $this->assertSame('baz', $settings->get('foo.bar'));
        $this->assertSame('qux', $settings->get('foo.baz'));
        $this->assertSame(1, $settings->get('foo.qux.foobar'));
        $this->assertSame(2, $settings->get('foo.qux.foobaz'));
        $this->assertSame(3, $settings->get('foo.qux.fooqux'));
        $this->assertSame(['state' => 'VT', 'flower' => 'rose', 'commute' => 'car'], $settings->get('bar'));
    }

    public function testEnvironmentOverrideReturnsOverriddenSettings() {
        /** @var Environment|MockObject $environment */
        $environment = $this->getMockBuilder(Environment::class)->getMock();
        $environment->expects($this->once())
            ->method('getType')
            ->willReturn(EnvironmentType::Development());
        $settings = $this->subject->loadSettings($environment);


        $this->assertSame('baz', $settings->get('foo.bar'));
        $this->assertSame('qux', $settings->get('foo.baz'));
        $this->assertSame(1000, $settings->get('foo.qux.foobar'));
        $this->assertSame(2, $settings->get('foo.qux.foobaz'));
        $this->assertSame(-3, $settings->get('foo.qux.fooqux'));
        $this->assertSame(['state' => 'VT', 'flower' => 'rose', 'commute' => 'car'], $settings->get('bar'));
        $this->assertSame('file', $settings->get('dev-setting.php'));
        $this->assertSame('file', $settings->get('dev-setting.json'));
    }

    public function testDefaultSettingsFileNotHandleableThrowsException() {
        /** @var Environment|MockObject $environment */
        $environment = $this->getMockBuilder(Environment::class)->getMock();
        $environment->expects($this->never())->method('getType');
        $fileHandler = new ChainedSettingsStorageHandler(new PhpFileSystemSettingsStorageHandler());
        $subject = new DefaultsWithEnvironmentOverrideSettingsLoader($fileHandler, $this->settingsPath, $this->environmentConfigDir);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to load settings for path "' . $this->settingsPath . '". This path is unsupported by any configured SettingsStorageHandler.');
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_PATH_UNSUPPORTED);

        $subject->loadSettings($environment);
    }
}
