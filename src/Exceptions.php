<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\DependencyInjectionException;
use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;

use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
use Ds\Map;
use Ds\Pair;
use Throwable;

/**
 * A class that is responsible for creating exceptions with known messages and errors code when exceptional occurrences
 * happen within Labrador.
 *
 * The msgArg annotations on the error codes are the argument types, in order, that are expected when creating an
 * exception with the given code. If the message does not take any dynamic parameters there will only be a single
 * msgArg annotation and its type will be void.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
class Exceptions {

    /**
     * An error code that is triggered when an Engine is attempted to run while in an invalid EngineState.
     *
     * @var int Exception code when this error occurs
     * @msgArg void
     */
    const ENGINE_ERR_MULTIPLE_RUN_CALLS = 1000;

    /**
     * An error code that is triggered when a Plugin attempts to be registered multiple times.
     *
     * @var int Exception code when this error occurs
     * @msgArg string The name of the Plugin that has already been registered
     */
    const PLUGIN_ERR_HAS_BEEN_REGISTERED = 1100;

    /**
     * An error code that is triggered when you attempt to register a Plugin after Plugins have already been loaded.
     *
     * @var int Exception code when this error occurs
     * @msgArg void
     */
    const PLUGIN_ERR_REGISTER_PLUGIN_POSTLOAD = 1101;

    /**
     * An error code when a Plugin is registered or when a PluginDependentPlugin depends on a type that does not
     * implement the Plugin interface.
     *
     * @var int Exception code when this error occurs
     * @msgArg string The name of the Plugin that does not implement the correct Plugin interface
     */
    const PLUGIN_ERR_REGISTER_NOT_PLUGIN_TYPE = 1102;

    /**
     * An error code when a Plugin is attempted to be fetched and could not be found.
     *
     * @var int Exception code when this error occurs.
     * @msgArg string The name of the Plugin attempting to fetch
     */
    const PLUGIN_ERR_PLUGIN_NOT_FOUND = 1103;

    /**
     * An error code when attempting to access a loaded Plugin before the loading process occurs.
     *
     * @var int Exception code when this error occurs.
     * @msgArg void
     */
    const PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD = 1104;

    /**
     * An error code when a PluginDependentPlugin has dependencies that would result in a circular dependency.
     *
     * @var int Exception code when this error occurs.
     * @msgArg string The name of the Plugin being loaded
     * @msgArg string The name of the dependent Plugin that has a circular dependency
     */
    const PLUGIN_ERR_CIRCULAR_DEPENDENCY = 1105;

    /**
     * An error code when a PluginDependentPlugin depends on a type that does not implement the Plugin interface.
     *
     * @var int Exception code when this error occurs.
     * @msgArg string The name of the Plugin being loaded
     * @msgArg string The name of the dependent Plugin that is not a Plugin type
     */
    const PLUGIN_ERR_DEPENDENCY_NOT_PLUGIN_TYPE = 1106;

    /**
     * An error code when a Labrador JSON configuration file is provided but it does not adhere to the json-schema for
     * configurations.
     *
     * @var int Exception code when this error occurs.
     * @msgArg void
     */
    const CONFIG_ERR_JSON_INVALID_SCHEMA = 1200;

    /**
     * An error code when a Labrador XML configuration file is provided but it does not adhere to XML schema for
     * configurations.
     *
     * @var int Exception code when this error occurs
     * @msgArg void
     */
    const CONFIG_ERR_XML_INVALID_SCHEMA = 1201;

    /**
     * An error code when a Labrador PHP configuration file is provided but it does not return either an array or a
     * Configuration instance.
     *
     * @var int Exception code when this error occurs
     * @msgArg void
     */
    const CONFIG_ERR_PHP_INVALID_RETURN_TYPE = 1202;

    /**
     * An error code when a Labrador configuration file could not be found at the given location.
     *
     * @var int Exception code when this error occurs
     * @msgArg void
     */
    const CONFIG_ERR_FILE_NOT_EXIST = 1203;

    /**
     * An error code when a Labrador configuration file is provided but is not a supported file type.
     *
     * @var int Exception code when this error occurs
     * @msgArg void
     */
    const CONFIG_ERR_FILE_UNSUPPORTED_EXTENSION = 1204;

    /**
     * An error code when an error occurs with creating the Auryn Injector for the library's DependencyGraph.
     *
     *@var int Exception code when this error occurs
     *@msgArg InjectorException
     */
    const DEPENDENCY_GRAPH_INJECTION_ERR = 2000;

    /**
     * @var Map
     */
    private static $codeMsgMap;

    private function __construct() {
    }

    /**
     * Create an Exception that has been mapped to a given error code, allowing the nesting of an exception as well
     * as customizing the message.
     *
     * If the $errorCode is not one that has been mapped to a known exception an InvalidArgumentException will be thrown
     *
     * @param int $errorCode
     * @param Throwable|null $nestedException
     * @param mixed ...$msgArgs
     * @return Exception A Labrador Exception
     */
    public static function createException(int $errorCode, Throwable $nestedException = null, ...$msgArgs) : Exception {
        if (!isset(self::$codeMsgMap)) {
            self::loadCodeMessageMap();
        }

        if (self::$codeMsgMap->hasKey($errorCode)) {
            $pair = self::$codeMsgMap->get($errorCode);
            $msg = ($pair->value)(...$msgArgs);
            return new $pair->key($msg, $errorCode, $nestedException);
        }

        return new Exception("An unknown error code was encountered", $errorCode, $nestedException);
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

        $map->put(
            self::DEPENDENCY_GRAPH_INJECTION_ERR,
            new Pair(
                DependencyInjectionException::class,
                function() {
                    return 'An error occurred creating the appropriate Injector for the DependencyGraph';
                }
            )
        );
    }
}
