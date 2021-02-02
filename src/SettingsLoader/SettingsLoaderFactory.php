<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Cspray\Labrador\EnvironmentType;

class SettingsLoaderFactory {

    public static function defaultFileSystemSettingsLoader(string $configDirectory, EnvironmentType $environment) : SettingsLoader {
    }

}