<?php declare(strict_types=1);


namespace Cspray\Labrador\SettingsLoader;

use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exceptions;
use SplFileInfo;

/**
 * An abstract SettingsStorageHandler that handles some of the boilerplate around dealing with files that are stored on
 * the local filesystem.
 *
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
abstract class AbstractFileSystemSettingsStorageHandler implements SettingsStorageHandler {

    /**
     * Ensures that the $path ends with an extension that matches the value returned from getExtension().
     *
     * @param string $path
     * @return bool
     */
    final public function canHandleSettingsPath(string $path) : bool {
        $fileInfo = new SplFileInfo($path);
        return $this->getExtension() === $fileInfo->getExtension();
    }

    /**
     * If the $filePath exists and is of a type that can be handled by this implementation delegate the actual loading
     * of settings to doLoad().
     *
     * If the $filePath does not exist or it cannot be handled by this implementation an exception will be thrown.
     *
     * @param string $filePath
     * @return array
     * @throws InvalidArgumentException
     */
    final public function loadSettings(string $filePath) : array {
        $fileInfo = new SplFileInfo($filePath);
        if (!$fileInfo->isFile()) {
            throw Exceptions::createException(Exceptions::SETTINGS_ERR_PATH_NOT_FOUND, null, $filePath);
        }

        if (!$this->canHandleSettingsPath($fileInfo->getPathname())) {
            throw Exceptions::createException(Exceptions::SETTINGS_ERR_PATH_UNSUPPORTED, null, $filePath);
        }

        return $this->doLoad($filePath);
    }

    /**
     * Return the extension that a given file must be to be able to be loaded by this implementation.
     *
     * @return string
     */
    abstract protected function getExtension() : string;

    /**
     * Do whatever is required to read in the file contents and convert the data into an array object.
     *
     * @param string $filePath
     * @return array
     */
    abstract protected function doLoad(string $filePath) : array;
}
