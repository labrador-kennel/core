<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Adbar\Dot;
use Cspray\Labrador\DotAccessSettings;
use Cspray\Labrador\Environment;
use Cspray\Labrador\Settings;

/**
 * Loads settings at a specific file path and then overrides any of those settings based on the EnvironmentType
 * the Application is currently running in.
 *
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
final class DefaultsWithEnvironmentOverrideSettingsLoader implements SettingsLoader {

    private $storageHandler;
    private $settingsFile;
    private $envDir;
    private $supportedFileTypes = [];

    public function __construct(
        SettingsStorageHandler $storageHandler,
        string $settingsFile,
        string $envDir,
        array $supportedFileTypes = ['php', 'json']
    ) {
        $this->storageHandler = $storageHandler;
        $this->settingsFile = $settingsFile;
        $this->envDir = $envDir;
        $this->supportedFileTypes = $supportedFileTypes;
    }

    public function loadSettings(Environment $environment) : Settings {
        $settings = new Dot($this->storageHandler->loadSettings($this->settingsFile));
        $envType = $environment->getType()->toString();
        foreach ($this->supportedFileTypes as $fileType) {
            $envPath = sprintf('%s/%s.%s', $this->envDir, $envType, $fileType);
            if (is_file($envPath)) {
                $envSettings = $this->storageHandler->loadSettings($envPath);
                $settings->mergeRecursiveDistinct($envSettings);
            }
        }

        return new DotAccessSettings($settings->all());
    }
}
