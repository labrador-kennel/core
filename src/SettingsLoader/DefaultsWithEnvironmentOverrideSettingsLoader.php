<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Adbar\Dot;
use Cspray\Labrador\DotAccessSettings;
use Cspray\Labrador\Environment;
use Cspray\Labrador\Settings;

/**
 * Loads settings at a specific file path and then overrides any of those settings based on the ApplicationEnvironment
 * the Application is currently running in.
 *
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
final class DefaultsWithEnvironmentOverrideSettingsLoader implements SettingsLoader {

    private $storageHandler;
    private $settingsConfiguration;

    public function __construct(SettingsStorageHandler $storageHandler, EnvironmentSettingsConfiguration $environmentSettingsConfiguration) {
        $this->storageHandler = $storageHandler;
        $this->settingsConfiguration = $environmentSettingsConfiguration;
    }

    public function loadSettings(Environment $environment) : Settings {
        $settings = new Dot($this->storageHandler->loadSettings($this->settingsConfiguration->getDefaultPath()));
        $envPath = $this->settingsConfiguration->getPathForApplicationEnvironment($environment->getApplicationEnvironment());
        if (isset($envPath)) {
            $envSettings = $this->storageHandler->loadSettings($envPath);
            $settings->mergeRecursiveDistinct($envSettings);
        }
        return new DotAccessSettings($settings->all());
    }
}
