<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Labrador\Exception\CircularDependencyException;
use Cspray\Labrador\Exception\DependencyInjectionException;
use Cspray\Labrador\Exception\Exception;
use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidStateException;

use Cspray\Labrador\Exception\InvalidTypeException;
use Cspray\Labrador\Exception\NotFoundException;
use Cspray\Labrador\Plugin\Pluggable;
use Cspray\Labrador\Plugin\Plugin;
use Throwable;

/**
 * A class that is responsible for creating exceptions with known messages and error codes when exceptional occurrences
 * happen within Labrador.
 *
 * The msgArg annotations on the error codes are the argument types, in order, that are expected when creating an
 * exception with the given code. If the message does not take any dynamic parameters there will only be a single
 * msgArg annotation and its type will be void.
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 * @internal
 */
final class Exceptions {

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
     * An error code when a Labrador configuration file is provided but is not a supported file type.
     *
     * @var int Exception code when this error occurs
     * @msgArg $path The settings path that is not supported by a SettingsStorageHandler
     */
    const SETTINGS_ERR_PATH_UNSUPPORTED = 1200;

    /**
     * An error code when a Labrador configuration file could not be found at the given location.
     *
     * @var int Exception code when this error occurs
     * @msgArg void
     */
    const SETTINGS_ERR_PATH_NOT_FOUND = 1201;

    /**
     * An error code when a Labrador PHP configuration file is provided but it does not return either an array or a
     * Configuration instance.
     *
     * @var int Exception code when this error occurs
     * @msgArg $path The path that returned the invalid type
     * @msgArg $type The type of the value that was returned
     */
    const SETTINGS_ERR_PHP_INVALID_RETURN_TYPE = 1202;

    /**
     * An error code when a Labrador JSON settings file is provided but it does not parse into an appropriate PHP data
     * structure.
     *
     * @var int Exception code when this error occurs
     * @msgArg $path The path that returned the invalid type
     *
     */
    const SETTINGS_ERR_JSON_INVALID_RETURN_TYPE = 1203;

    const SETTINGS_ERR_ENV_VAR_OVERRIDE_NOT_FOUND = 1204;

    const SETTINGS_ERR_KEY_NOT_FOUND = 1205;

    const APP_ERR_MULTIPLE_START_CALLS = 1300;

    /**
     * An error code when an error occurs with creating the Auryn Injector for the library's DependencyGraph.
     *
     * @var int Exception code when this error occurs
     * @msgArg InjectorException
     */
    const DEPENDENCY_GRAPH_INJECTION_ERR = 2000;

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
     * @return Exception|NotFoundException A Labrador Exception
     */
    public static function createException(int $errorCode, Throwable $nestedException = null, ...$msgArgs) : Exception {
        if (!isset(self::$codeMsgMap)) {
            self::loadCodeMessageMap();
        }

        if (array_key_exists($errorCode, self::$codeMsgMap)) {
            $pair = self::$codeMsgMap[$errorCode];
            $msg = ($pair[1])(...$msgArgs);
            return new $pair[0]($msg, $errorCode, $nestedException);
        }

        return new Exception("An unknown error code was encountered", $errorCode, $nestedException);
    }

    private static function loadCodeMessageMap() {
        $map = [];

        $invalidState = InvalidStateException::class;
        $invalidArg = InvalidArgumentException::class;

        $map[self::ENGINE_ERR_MULTIPLE_RUN_CALLS] = [
            $invalidState,
            function() {
                return sprintf('%s::%s MUST NOT be called while already running.', Engine::class, 'run');
            }
        ];

        $map[self::PLUGIN_ERR_HAS_BEEN_REGISTERED] = [
            $invalidArg,
            function(string $pluginName) {
                return sprintf(
                    'A Plugin with name %s has already been registered and may not be registered again.',
                    $pluginName
                );
            }
        ];

        $map[self::PLUGIN_ERR_REGISTER_PLUGIN_POSTLOAD] = [
            $invalidState,
            function() {
                return 'Plugins have already been loaded and you MUST NOT register plugins after this has taken place.';
            }
        ];

        $map[self::PLUGIN_ERR_REGISTER_NOT_PLUGIN_TYPE] = [
            $invalidArg,
            function(string $plugin) {
                return sprintf(
                    'Attempted to register a Plugin, %s, that does not implement the %s interface',
                    $plugin,
                    Plugin::class
                );
            }
        ];

        $map[self::PLUGIN_ERR_PLUGIN_NOT_FOUND] = [
            NotFoundException::class,
            function(string $plugin) {
                return sprintf('Could not find a Plugin named "%s"', $plugin);
            }
        ];

        $map[self::PLUGIN_ERR_INVALID_PLUGIN_ACCESS_PRELOAD] = [
            $invalidState,
            function() {
                return sprintf(
                    'Loaded Plugins may only be gathered after %s::loadPlugins has been invoked',
                    Pluggable::class
                );
            }
        ];

        $map[self::PLUGIN_ERR_CIRCULAR_DEPENDENCY] = [
            CircularDependencyException::class,
            function(string $plugin, string $dependentPlugin) {
                return sprintf(
                    'A circular dependency was found with %s requiring %s.',
                    $plugin,
                    $dependentPlugin
                );
            }
        ];

        $map[self::PLUGIN_ERR_DEPENDENCY_NOT_PLUGIN_TYPE] = [
            $invalidState,
            function(string $plugin, string $dependentPlugin) {
                return sprintf(
                    'A Plugin, %s, depends on a type, %s, that does not implement %s',
                    $plugin,
                    $dependentPlugin,
                    Plugin::class
                );
            }
        ];

        $map[self::SETTINGS_ERR_PHP_INVALID_RETURN_TYPE] = [
            InvalidTypeException::class,
            function(string $path, string $type) {
                return sprintf(
                    'The type returned from a PHP settings file MUST be an array but "%s" returned a "%s".',
                    $path,
                    $type
                );
            }
        ];

        $map[self::SETTINGS_ERR_JSON_INVALID_RETURN_TYPE] = [
            InvalidTypeException::class,
            function(string $path, string $type) {
                return sprintf(
                    'The type returned from a JSON settings file MUST be a JSON object but "%s" was parsed into a "%s".',
                    $path,
                    $type,
                );
            }
        ];

        $map[self::SETTINGS_ERR_PATH_NOT_FOUND] = [
            $invalidArg,
            function(string $path) {
                return sprintf('The settings path "%s" could not be found.', $path);
            }
        ];

        $map[self::SETTINGS_ERR_PATH_UNSUPPORTED] = [
            $invalidArg,
            function(string $path) {
                $message = 'Unable to load settings for path "%s". This path is unsupported by any configured SettingsStorageHandler.';
                return sprintf($message, $path);
            }
        ];

        $map[self::SETTINGS_ERR_ENV_VAR_OVERRIDE_NOT_FOUND] = [
            NotFoundException::class,
            function(string $envVar) {
                return sprintf('Expected environment variable "%s" to have a value but it was null.', $envVar);
            }
        ];

        $map[self::SETTINGS_ERR_KEY_NOT_FOUND] = [
            NotFoundException::class,
            function(string $key) {
                return sprintf('The setting "%s" could not be found.', $key);
            }
        ];

        $map[self::DEPENDENCY_GRAPH_INJECTION_ERR] = [
            DependencyInjectionException::class,
            function() {
                return 'An error occurred creating the appropriate Injector for the DependencyGraph';
            }
        ];

        $map[self::APP_ERR_MULTIPLE_START_CALLS] = [
            InvalidStateException::class,
            function() {
                return sprintf('%s::start MUST NOT be called while the Application is in a started or crashed state.', Application::class);
            }
        ];

        self::$codeMsgMap = $map;
    }
}
