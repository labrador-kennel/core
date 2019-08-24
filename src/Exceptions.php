<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\EngineAlreadyRunningException;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;

use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
use Ds\Map;
use Ds\Pair;
use Throwable;

/**
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
class Exceptions {

    const ENGINE_ERR_MULTIPLE_RUN_CALLS = 1000;

    /**
     * An error code that is triggered when a Plugin attempts to be registered multiple times.
     *
     * @var int Exception code when this error occurs
     */
    const PLUGIN_ERR_HAS_BEEN_REGISTERED = 1100;

    /**
     * An error code that is triggered when you attempt to register a Plugin after Plugins have already been loaded.
     *
     * @var int Exception code when this error occurs
     */
    const PLUGIN_ERR_REGISTER_PLUGIN_POSTLOAD = 1101;

    /**
     * An error code when a Plugin is registered or when a PluginDependentPlugin depends on a type that does not
     * implement the Plugin interface.
     *
     * @var int Exception code when this error occurs
     */
    const PLUGIN_ERR_REGISTER_NOT_PLUGIN_TYPE = 1102;

    /**
     * An error code when a Plugin is attempted to be fetched and could not be found.
     *
     * @var int Exception code when this error occurs.
     */
    const PLUGIN_ERR_PLUGIN_NOT_FOUND = 1103;

    /**
     * An error code when attempting to access a loaded Plugin before the loading process occurs.
     *
     * @var int Exception code when this error occurs.
     */
    const PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD = 1104;

    /**
     * An error code when a PluginDependentPlugin has dependencies that would result in a circular dependency.
     *
     * @var int Exception code when this error occurs.
     */
    const PLUGIN_ERR_CIRCULAR_DEPENDENCY = 1105;

    /**
     * An error code when a PluginDependentPlugin depends on a type that does not implement the Plugin interface.
     *
     * @var int Exception code when this error occurs.
     */
    const PLUGIN_ERR_DEPENDENCY_NOT_PLUGIN_TYPE = 1106;

    const CONFIG_ERR_JSON_INVALID_SCHEMA = 1200;

    const CONFIG_ERR_XML_INVALID_SCHEMA = 1201;

    const CONFIG_ERR_PHP_INVALID_RETURN_TYPE = 1202;

    const CONFIG_ERR_FILE_NOT_EXIST = 1203;

    const CONFIG_ERR_FILE_UNSUPPORTED_EXTENSION = 1204;

    /**
     * @var Map
     */
    private static $codeMsgMap;

    private function __construct() {
    }

    public static function createException(int $errorCode, Throwable $nestedException = null, ...$msgArgs) : Throwable {
        if (!isset(self::$codeMsgMap)) {
            self::loadCodeMessageMap();
        }
        if (self::$codeMsgMap->hasKey($errorCode)) {
            $pair = self::$codeMsgMap->get($errorCode);
            $msg = ($pair->value)(...$msgArgs);
            return new $pair->key($msg, $errorCode, $nestedException);
        }

        throw new InvalidArgumentException('The error code provided is not mapped to a known exception');
    }

    private static function loadCodeMessageMap() {
        self::$codeMsgMap = $map = new Map();

        $invalidState = InvalidStateException::class;
        $invalidArg = InvalidArgumentException::class;

        $map->put(self::ENGINE_ERR_MULTIPLE_RUN_CALLS, new Pair($invalidState, function() {
            return sprintf('%s::%s MUST NOT be called while already running.', Engine::class, 'run');
        }));

        $map->put(self::PLUGIN_ERR_HAS_BEEN_REGISTERED, new Pair($invalidArg, function(string $pluginName) {
            return sprintf(
                'A Plugin with name %s has already been registered and may not be registered again.',
                $pluginName
            );
        }));

        $map->put(self::PLUGIN_ERR_REGISTER_PLUGIN_POSTLOAD, new Pair($invalidState, function() {
            return 'Plugins have already been loaded and you MUST NOT register plugins after this has taken place.';
        }));

        $map->put(self::PLUGIN_ERR_REGISTER_NOT_PLUGIN_TYPE, new Pair($invalidArg, function(string $plugin) {
            return sprintf(
                'Attempted to register a Plugin, %s, that does not implement the %s interface',
                $plugin,
                Plugin::class
            );
        }));

        $map->put(self::PLUGIN_ERR_PLUGIN_NOT_FOUND, new Pair(NotFoundException::class, function(string $plugin) {
            return sprintf('Could not find a Plugin named "%s"', $plugin);
        }));

        $map->put(self::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD, new Pair($invalidState, function() {
            return sprintf(
                'Loaded Plugins may only be gathered after %s::loadPlugins has been invoked',
                Pluggable::class
            );
        }));

        $map->put(
            self::PLUGIN_ERR_CIRCULAR_DEPENDENCY,
            new Pair(
                CircularDependencyException::class,
                function(string $plugin, string $dependentPlugin) {
                    return sprintf(
                        'A circular dependency was found with %s requiring %s.',
                        $plugin,
                        $dependentPlugin
                    );
                }
            )
        );

        $map->put(
            self::PLUGIN_ERR_DEPENDENCY_NOT_PLUGIN_TYPE,
            new Pair($invalidState, function(string $plugin, string $dependentPlugin) {
                return sprintf(
                    'A Plugin, %s, depends on a type, %s, that does not implement %s',
                    $plugin,
                    $dependentPlugin,
                    Plugin::class
                );
            })
        );

        $map->put(self::CONFIG_ERR_JSON_INVALID_SCHEMA, new Pair($invalidState, function() {
            return 'The configuration provided does not validate against the required JSON schema.';
        }));

        $map->put(self::CONFIG_ERR_XML_INVALID_SCHEMA, new Pair($invalidState, function() {
            return 'The configuration provided does not validate against the required XML schema.';
        }));

        $map->put(self::CONFIG_ERR_PHP_INVALID_RETURN_TYPE, new Pair($invalidState, function() {
            return sprintf(
                'The configuration provided does not return a valid PHP array or %s instance',
                Configuration::class
            );
        }));

        $map->put(self::CONFIG_ERR_FILE_NOT_EXIST, new Pair($invalidArg, function() {
            return 'The configuration provided is not a valid file path that can be read from.';
        }));

        $map->put(self::CONFIG_ERR_FILE_UNSUPPORTED_EXTENSION, new Pair($invalidArg, function() {
            return 'The file extension for the provided configuration is not supported.';
        }));
    }
}
