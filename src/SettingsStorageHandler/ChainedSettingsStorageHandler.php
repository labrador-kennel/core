<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsStorageHandler;

use Cspray\Labrador\SettingsStorageHandler;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exceptions;

/**
 * A SettingsStorageHandler that facilitates loading settings information from a variety of storage locations.
 *
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
final class ChainedSettingsStorageHandler implements SettingsStorageHandler {

    private $handlers;

    /**
     * @param SettingsStorageHandler ...$handlers
     */
    public function __construct(SettingsStorageHandler...$handlers) {
        $this->handlers = $handlers;
    }

    /**
     * Facilitates adding custom SettingsStorageHandler implementations that do not come out of the box with Labrador.
     *
     * @param SettingsStorageHandler $settingsFileHandler
     */
    public function addSettingsFileHandler(SettingsStorageHandler $settingsFileHandler) : void {
        $this->handlers[] = $settingsFileHandler;
    }

    /**
     * Returns true if any of the attached $handlers can handle the given $path.
     *
     * @param string $path
     * @return bool
     */
    public function canHandleSettingsPath(string $path) : bool {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandleSettingsPath($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Loads settings from the first configured handler that can handle the given $path.
     *
     * @param string $path
     * @return array
     * @throws InvalidArgumentException
     */
    public function loadSettings(string $path) : array {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandleSettingsPath($path)) {
                return $handler->loadSettings($path);
            }
        }

        throw Exceptions::createException(Exceptions::SETTINGS_ERR_PATH_UNSUPPORTED, null, $path);
    }
}
