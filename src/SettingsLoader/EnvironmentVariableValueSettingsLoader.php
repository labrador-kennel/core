<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Cspray\Labrador\DotAccessSettings;
use Cspray\Labrador\Environment;
use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\Settings;
use Cspray\Labrador\SettingsLoader;

/**
 * A SettingsLoader that decorates a SettingsLoader to allow for the defining of Settings through environment variables.
 *
 * This SettingsLoader works by looking at all of the entries in the loaded Settings object for a value that looks like:
 * `!env(VAR_NAME)` where `VAR_NAME` is the name of the environment variable that should be used to generate the actual
 * value. This allows you to define configuration with access to environment variables, even if that configuration
 * format does not support executing PHP code. Here's an example in JSON:
 *
 * {
 *      "dbHost": "!env(DB_HOST)",
 *      "dbUser": "!env(DB_USER)",
 *      "dbPass": "!env(DB_PASS)"
 * }
 *
 * In each case the Environment would be queried to determine if it has a value for that key. If a value is not found
 * for that environment variable an exception will be thrown as these values are expected to be an integral part of your
 * application and that something will break with further along your app's execution where it could cause more problems.
 *
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
final class EnvironmentVariableValueSettingsLoader implements SettingsLoader {

    private const ENV_VAR_OVERRIDE_PATTERN = '/^!env\((?P<env_var>.+)\)$/';

    private $settingsLoader;

    public function __construct(SettingsLoader $settingsLoader) {
        $this->settingsLoader = $settingsLoader;
    }

    /**
     * @param Environment $environment
     * @return Settings
     * @throws Exception|NotFoundException
     */
    public function loadSettings(Environment $environment) : Settings {
        $settings = $this->settingsLoader->loadSettings($environment);
        // We need a reference to this variable in the function because we're calling it recursively
        $setEnvVars = function(array $items) use($environment, &$setEnvVars) {
            $cleanItems = [];
            foreach ($items as $key => $value) {
                if (is_array($value)) {
                    $cleanItems[$key] = $setEnvVars($value);
                } elseif (preg_match(self::ENV_VAR_OVERRIDE_PATTERN, $value, $matches)) {
                    $envValue = $environment->getVar($matches['env_var']);
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
