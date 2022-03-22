<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Adbar\Dot;
use Cspray\Labrador\DotAccessSettings;
use Cspray\Labrador\Environment;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\Settings;
use Cspray\Labrador\SettingsLoader;
use Cspray\Labrador\SettingsStorageHandler;

/**
 * Loads settings at a specific file path and then overrides any of those settings based on the EnvironmentType
 * the Application is currently running in.
 *
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
final class DefaultsWithEnvironmentOverrideSettingsLoader implements SettingsLoader {

    private SettingsStorageHandler $storageHandler;
    private string $settingsFile;
    private string $envDir;

    public function __construct(
        SettingsStorageHandler $storageHandler,
        string $settingsFile,
        string $envDir
    ) {
        $this->storageHandler = $storageHandler;
        $this->settingsFile = $settingsFile;
        $this->envDir = $envDir;
    }

    public function loadSettings(Environment $environment) : Settings {
        $settings = new Dot($this->storageHandler->loadSettings($this->settingsFile));
        $envType = $environment->getType()->toString();
        $envPath = sprintf('%s/%s.*', $this->envDir, $envType);
        $envFiles = glob($envPath);

        if (count($envFiles) > 1) {
            throw Exceptions::createException(Exceptions::SETTINGS_ERR_MULTIPLE_ENVIRONMENT_CONFIGS, null, $envType);
        } elseif (count($envFiles) === 1) {
            $envSettings = $this->storageHandler->loadSettings($envFiles[0]);
            $settings->mergeRecursiveDistinct($envSettings);
        }

        return new DotAccessSettings($settings->all());
    }
}
