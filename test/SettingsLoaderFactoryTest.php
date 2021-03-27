<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\SettingsLoader\SettingsLoaderFactory;
use Cspray\Labrador\StandardEnvironment;
use PHPUnit\Framework\TestCase;

class SettingsLoaderFactoryTest extends TestCase {

    private $environment;

    public function setUp() : void {
        parent::setUp();
        $this->environment = new StandardEnvironment(EnvironmentType::Test(), ['USER' => 'labrador-kennel-user']);
    }

    public function testConfigDirectoryDoesNotExistThrowsException() {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Attempted to create a default filesystem SettingsLoader but the config directory "/bad/dir" could not be found.');

        SettingsLoaderFactory::defaultFileSystemSettingsLoader('/bad/dir');
    }

    public function testConfigDirectoryIsFileThrowsException() {
        $configPath = __DIR__ . '/resources/config/settings.json';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Attempted to create a default filesystem SettingsLoader but the config directory "' . $configPath . '" is a file.');

        SettingsLoaderFactory::defaultFileSystemSettingsLoader($configPath);
    }

    public function testConfigDirectoryHasMultipleSettingsFileThrowsException() {
        $configPath = __DIR__ . '/resources/duplicate-config';
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('Attempted to create a default filesystem SettingsLoader but the config directory "' . $configPath . '" has multiple main settings files. Please reduce the number of main settings files in the config directory to a maximum of 1.');

        SettingsLoaderFactory::defaultFileSystemSettingsLoader($configPath);
    }

    public function testConfigDirectoryIsEmptyReturnsEmptySettings() {
        $configPath = __DIR__ . '/resources/empty-config';
        $settingsLoader = SettingsLoaderFactory::defaultFileSystemSettingsLoader($configPath);

        $settings = $settingsLoader->loadSettings($this->environment);

        $settingsAsArray = iterator_to_array($settings);

        $this->assertEmpty($settingsAsArray);
    }

    public function testConfigDirectoryIsNotEmptyReturnsCorrectSettings() {
        $configPath = __DIR__ . '/resources/config';
        $settingsLoader = SettingsLoaderFactory::defaultFileSystemSettingsLoader($configPath);

        $settings = $settingsLoader->loadSettings($this->environment);

        $this->assertEquals($settings->get('foo.qux.foobar'), 1000);
        $this->assertEquals($settings->get('foo.qux.foobaz'), 2);
        $this->assertEquals($settings->get('foo.qux.fooqux'), 'labrador-kennel-user');
    }

}