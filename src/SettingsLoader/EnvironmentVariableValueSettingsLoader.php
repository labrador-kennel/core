<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Cspray\Labrador\DotAccessSettings;
use Cspray\Labrador\Environment;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\Settings;

/**
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
final class EnvironmentVariableValueSettingsLoader implements SettingsLoader {

    private $settingsLoader;

    public function __construct(SettingsLoader $settingsLoader) {
        $this->settingsLoader = $settingsLoader;
    }

    public function loadSettings(Environment $environment) : Settings {
        $settings = $this->settingsLoader->loadSettings($environment);
        $setEnvVars = function(array $items) use($environment, &$setEnvVars) {
            $cleanItems = [];
            foreach ($items as $key => $value) {
                if (is_array($value)) {
                    $cleanItems[$key] = $setEnvVars($value);
                } elseif (preg_match('/^!env\((?P<env_var>.+)\)$/', $value, $matches)) {
                    $envValue = $environment->getEnvironmentVariable($matches['env_var']);
                    if (is_null($envValue)) {
                        throw Exceptions::createException(Exceptions::SETTINGS_ERR_ENV_VAR_OVERRIDE_NOT_FOUND, null, $matches['env_var']);
                    }
                    $cleanItems[$key] = $envValue;
                } else {
                    $cleanItems[$key] = $value;
                }
            }
            return $cleanItems;
        };

        return new DotAccessSettings($setEnvVars(iterator_to_array($settings)));
    }
}
