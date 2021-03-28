<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsStorageHandler;

use Cspray\Labrador\SettingsStorageHandler;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\SettingsStorageHandler\AbstractFileSystemSettingsStorageHandler as FSSettingsStorageHandler;

/**
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
final class JsonFileSystemSettingsStorageHandler extends FSSettingsStorageHandler implements SettingsStorageHandler {

    protected function getExtension() : string {
        return 'json';
    }

    protected function doLoad(string $filePath) : array {
        $data = json_decode(file_get_contents($filePath), true);
        if (!is_array($data)) {
            throw Exceptions::createException(
                Exceptions::SETTINGS_ERR_JSON_INVALID_RETURN_TYPE,
                null,
                $filePath,
                gettype($data)
            );
        }

        return $data;
    }
}
