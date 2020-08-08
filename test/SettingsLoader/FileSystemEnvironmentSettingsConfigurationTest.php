<?php declare(strict_types=1);


namespace Cspray\Labrador\Test\SettingsLoader;


use Cspray\Labrador\ApplicationEnvironment;
use Cspray\Labrador\SettingsLoader\FileSystemEnvironmentSettingsConfiguration;
use PHPUnit\Framework\TestCase;

class FileSystemEnvironmentSettingsConfigurationTest extends TestCase {

    private $settingsPath;
    private $environmentDir;

    public function setUp() : void {
        $this->settingsPath = dirname(__DIR__) . '/resources/config/settings.json';
        $this->environmentDir = dirname(__DIR__) . '/resources/config/environment';
    }

    public function testGetDefaultPathPassedToConstructor() {
        $subject = new FileSystemEnvironmentSettingsConfiguration($this->settingsPath, $this->environmentDir);

        $this->assertSame($this->settingsPath, $subject->getDefaultPath());
    }

    public function testGetPathForEnvironmentDefaultPriorityFileExists() {
        $subject = new FileSystemEnvironmentSettingsConfiguration($this->settingsPath, $this->environmentDir);

        $expected = sprintf('%s/development.php', $this->environmentDir);
        $this->assertSame($expected, $subject->getPathForApplicationEnvironment(ApplicationEnvironment::Development()));
    }

    public function testGetPathForEnvironmentSpecificPriorityFileExists() {
        $subject = new FileSystemEnvironmentSettingsConfiguration(
            $this->settingsPath, $this->environmentDir, ['json', 'php']
        );

        $expected = sprintf('%s/development.json', $this->environmentDir);
        $this->assertSame($expected, $subject->getPathForApplicationEnvironment(ApplicationEnvironment::Development()));
    }

    public function testGetPathForEnvironmentNotFoundReturnsNull() {
        $subject = new FileSystemEnvironmentSettingsConfiguration($this->settingsPath, $this->environmentDir);

        $this->assertNull($subject->getPathForApplicationEnvironment(ApplicationEnvironment::Production()));
    }

}