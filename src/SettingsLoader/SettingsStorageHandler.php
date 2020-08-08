<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

/**
 * Utilized by SettingsLoader implementations to delegate the actual responsibility of performing the I/O operations
 * necessary to load settings.
 *
 * Generally implementations should plan on loading settings from a file on disk or some other location that may require
 * I/O. It is important to note that loading settings is one of the few places in Labrador where the functionality is
 * expected to be synchronous and makes use of blocking constructs. The decision to use blocking I/O for this is because
 * it is expected that the normal use case for bootstrapping will require some amount of information from Settings
 * before the Loop has started running. Since the Loop is running we don't need to worry about blocking any asynchronous
 * tasks that may be queued. Additionally, any time spent waiting for I/O impacts your application boot time, not your
 * runtime. However, it is critical that you DO NOT load settings once your application has started or it could
 * potentially cause serious performance degradation as you introduce blocking I/O into the Loop.
 *
 * While the expected and most common use case is for the settings storage to be a local file system this may not always
 * be the case. It is important to keep in mind that these handler implementations are not tied to any specific storage
 * mechanism or storage format. As long the data can be converted into an associative array anything goes. If you are
 * implementing a custom SettingsStorageHandler and it is stored on the local filesystem you should check out
 * AbstractFileSystemSettingsStorageHandler to reduce some boilerplate when loading files from the local filesystem.
 *
 * @package Cspray\Labrador\SettingsLoader
 * @license See LICENSE in source root
 */
interface SettingsStorageHandler {

    /**
     * Return whether or not this implementation believes it can safely handle the storage type for the given $path.
     *
     * It is important to note there that you should not be doing any I/O operations within this method directly. String
     * interrogations should be sufficient for the implementation to determine whether or not it could load the
     * settings. Whether or not the storage actually exists at this path or there are some other problems with the
     * actual data or storage mechanism is not meant for this method to determine.
     *
     * @param string $path
     * @return bool
     */
    public function canHandleSettingsPath(string $path) : bool;

    /**
     * Turns arbitrary data stored in $path to an array suitable for later conversion into a Settings object.
     *
     * !! WARNING !!
     *
     * It is expected that this method may make use of blocking I/O! Please use caution when invoking this method and
     * ensure that it is not invoked within a running Loop.
     *
     * @param string $path
     * @return array
     */
    public function loadSettings(string $path) : array;
}
