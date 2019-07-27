<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\EngineAlreadyRunningException;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;

use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
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

    private static $codeMsgMap = [];

    private function __construct() {
    }

    public static function createException(int $errorCode, Throwable $nestedException = null, ...$msgArguments) : Throwable {
        if (empty(self::$codeMsgMap)) {
            self::loadCodeMessageMap();
        }
        $errorInfo = self::$codeMsgMap[$errorCode] ?? null;
        $type = $errorInfo['type'];
        $messageCallback = $errorInfo['message'];

        return new $type($messageCallback(...$msgArguments), $errorCode, $nestedException);
    }

    private static function loadCodeMessageMap() {
        self::$codeMsgMap[self::ENGINE_ERR_MULTIPLE_RUN_CALLS] = [
            'type' => InvalidStateException::class,
            'message' => function() {
                return sprintf(
                    '%s::%s MUST NOT be called while already running.',
                    Engine::class,
                    'run'
                );
            }
        ];

        self::$codeMsgMap[self::PLUGIN_ERR_HAS_BEEN_REGISTERED] = [
            'type' => InvalidArgumentException::class,
            'message' => function(string $pluginName) {
                return sprintf(
                    'A Plugin with name %s has already been registered and may not be registered again.',
                    $pluginName
                );
            }
        ];

        self::$codeMsgMap[self::PLUGIN_ERR_REGISTER_PLUGIN_POSTLOAD] = [
            'type' => InvalidStateException::class,
            'message' => function() {
                return 'Plugins have already been loaded and you MUST NOT register plugins after this has taken place.';
            }
        ];

        self::$codeMsgMap[self::PLUGIN_ERR_REGISTER_NOT_PLUGIN_TYPE] = [
            'type' => InvalidArgumentException::class,
            'message' => function(string $plugin) {
                return sprintf(
                    'Attempted to register a Plugin, %s, that does not implement the %s interface',
                    $plugin,
                    Plugin::class
                );
            }
        ];

        self::$codeMsgMap[self::PLUGIN_ERR_PLUGIN_NOT_FOUND] = [
            'type' => NotFoundException::class,
            'message' => function(string $plugin) {
                return sprintf('Could not find a Plugin named "%s"', $plugin);
            }
        ];

        self::$codeMsgMap[self::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD] = [
            'type' => InvalidStateException::class,
            'message' => function() {
                return sprintf(
                    'Loaded Plugins may only be gathered after %s::loadPlugins has been invoked',
                    Pluggable::class
                );
            }
        ];

        self::$codeMsgMap[self::PLUGIN_ERR_CIRCULAR_DEPENDENCY] = [
            'type' => CircularDependencyException::class,
            'message' => function(string $plugin, string $dependentPlugin) {
                return sprintf(
                    'A circular dependency was found with %s requiring %s.',
                    $plugin,
                    $dependentPlugin
                );
            }
        ];

        self::$codeMsgMap[self::PLUGIN_ERR_DEPENDENCY_NOT_PLUGIN_TYPE] = [
            'type' => InvalidStateException::class,
            'message' => function(string $plugin, string $dependentPlugin) {
                return sprintf(
                    'A Plugin, %s, depends on a type, %s, that does not implement %s',
                    $plugin,
                    $dependentPlugin,
                    Plugin::class
                );
            }
        ];

        self::$codeMsgMap[self::CONFIG_ERR_JSON_INVALID_SCHEMA] = [
            'type' => InvalidStateException::class,
            'message' => function() {
                return 'The configuration provided does not validate against the required JSON schema.';
            }
        ];

        self::$codeMsgMap[self::CONFIG_ERR_XML_INVALID_SCHEMA] = [
            'type' => InvalidStateException::class,
            'message' => function() {
                return 'The configuration provided does not validate against the required XML schema.';
            }
        ];

        self::$codeMsgMap[self::CONFIG_ERR_PHP_INVALID_RETURN_TYPE] = [
            'type' => InvalidStateException::class,
            'message' => function() {
                return sprintf(
                    'The configuration provided does not return a valid PHP array or %s instance',
                    Configuration::class
                );
            }
        ];

        self::$codeMsgMap[self::CONFIG_ERR_FILE_NOT_EXIST] = [
            'type' => InvalidArgumentException::class,
            'message' => function() {
                return 'The configuration provided is not a valid file path that can be read from.';
            }
        ];

        self::$codeMsgMap[self::CONFIG_ERR_FILE_UNSUPPORTED_EXTENSION] = [
            'type' => InvalidArgumentException::class,
            'message' => function() {
                return 'The file extension for the provided configuration is not supported.';
            }
        ];
    }
}
