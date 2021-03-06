<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsStorageHandler;

use Cspray\Labrador\SettingsStorageHandler;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\SettingsStorageHandler\AbstractFileSystemSettingsStorageHandler as FSSettingsStorageHandler;

/**
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
final class PhpFileSystemSettingsStorageHandler extends FSSettingsStorageHandler implements SettingsStorageHandler {

    protected function getExtension() : string {
        return 'php';
    }

    protected function doLoad(string $filePath) : array {
        $settings = include $filePath;
        if (!is_array($settings)) {
            throw Exceptions::createException(
                Exceptions::SETTINGS_ERR_PHP_INVALID_RETURN_TYPE,
                null,
                $filePath,
                gettype($settings)
            );
        }

        return $settings;
    }
}
