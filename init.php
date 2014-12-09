<?php

/**
 *
 * @license See LICENSE in source root
 * @version 0.1
 * @since   0.1
 */

set_error_handler(function($severity, $msg, $file, $line) {
    throw new ErrorException($msg, 0, $severity, $file, $line);
});
set_exception_handler(function(Exception $exception) {
    $type = get_class($exception);
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $stackTrace = $exception->getTraceAsString();
    $msg = <<<MSG
An exception was thrown!

Type: {$type}
Message: {$message} in {$file} on {$line}.

Stack Trace:

{$stackTrace}
MSG;

    fwrite(STDERR, $msg);
});

require_once __DIR__ . '/vendor/autoload.php';

use Labrador\Engine;
use Labrador\Plugin\PluginManager;
use Auryn\Provider;
use Symfony\Component\EventDispatcher\EventDispatcher;

$provider = new Provider();
$eventDispatcher = new EventDispatcher();
$pluginManager = new PluginManager($provider, $eventDispatcher);
$engine = new Engine($eventDispatcher, $pluginManager);

$eventDispatcher->addListener(Engine::PLUGIN_BOOT_EVENT, function() {
    fwrite(STDOUT, "Called the plugin boot event!\n");
});

$eventDispatcher->addListener(Engine::APP_EXECUTE_EVENT, function() {
    fwrite(STDOUT, "Your application should execute here!\n");
});

$eventDispatcher->addListener(Engine::PLUGIN_CLEANUP_EVENT, function() {
    fwrite(STDOUT, "You can do any final cleanup here\n");
});

$eventDispatcher->addListener(Engine::EXCEPTION_THROWN_EVENT, function() {
    fwrite(STDOUT, "We caught an exception thrown during one of the previously documented events. Do something about it!\n");
});

$engine->run();
