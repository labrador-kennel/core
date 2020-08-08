<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Cspray\Labrador\Environment;
use Cspray\Labrador\Settings;

/**
 * Generate a Settings object specific to an Environment.
 *
 * The loading of the settings information from the storage location should be delegated to implementations of
 * SettingsStorageHandler.
 *
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 * @see SettingsStorageHandler
 */
interface SettingsLoader {

    /**
     * Synchronously load settings from whatever storage is appropriate for the given Environment and return a
     * Settings object.
     *
     * @param Environment $environment
     * @return Settings
     */
    public function loadSettings(Environment $environment) : Settings;
}
