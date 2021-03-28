<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Cspray\Labrador\DotAccessSettings;
use Cspray\Labrador\Environment;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\Settings;
use Cspray\Labrador\SettingsLoader;
use Cspray\Labrador\SettingsStorageHandler\ChainedSettingsStorageHandler;
use Cspray\Labrador\SettingsStorageHandler\JsonFileSystemSettingsStorageHandler;
use Cspray\Labrador\SettingsStorageHandler\PhpFileSystemSettingsStorageHandler;

class SettingsLoaderFactory {

    public static function defaultFileSystemSettingsLoader(string $configDirectory) : SettingsLoader {
        if (is_file($configDirectory)) {
            throw Exceptions::createException(Exceptions::SETTINGS_ERR_CONFIG_DIRECTORY_IS_FILE, null, $configDirectory);
        } elseif (!is_dir($configDirectory)) {
            throw Exceptions::createException(Exceptions::SETTINGS_ERR_CONFIG_DIRECTORY_NOT_FOUND, null, $configDirectory);
        } else {
            $settingsFiles = glob(sprintf('%s/settings.*', $configDirectory));
            if (count($settingsFiles) > 1) {
                $msg = 'Attempted to create a default filesystem SettingsLoader but the config directory "%s" has multiple main settings files. Please reduce the number of main settings files in the config directory to a maximum of 1.';
                throw new InvalidStateException(sprintf($msg, $configDirectory));
            } elseif (count($settingsFiles) === 1) {
                $storageHandler = new ChainedSettingsStorageHandler(
                    new PhpFileSystemSettingsStorageHandler(),
                    new JsonFileSystemSettingsStorageHandler()
                );
                $loader = new DefaultsWithEnvironmentOverrideSettingsLoader(
                    $storageHandler,
                    $settingsFiles[0],
                    sprintf('%s/environment', $configDirectory)
                );

                return new EnvironmentVariableValueSettingsLoader($loader);
            } else {
                return new class implements SettingsLoader {
                    public function loadSettings(Environment $environment) : Settings {
                        return new DotAccessSettings([]);
                    }
                };
            }
        }
    }
}
