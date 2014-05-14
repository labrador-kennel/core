<?php

/**
 * 
 * @author  Charles Sprayberry
 * @license See LICENSE in source root
 * @version 0.1
 * @since   0.1
 */

set_error_handler(function($severity, $msg, $file, $line) {
    throw new ErrorException($msg, 0, $severity, $file, $line);
});
set_exception_handler(function(Exception $exception) {
    http_response_code(500);
    $msg = htmlspecialchars($exception->getMessage());
    echo <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <title>Labrador Error</title>
    </head>
    <body>
        <h1>Internal Server Error</h1>
        <p>An error was encountered processing your request. Please contact the administrator.</p>
        <p>The error message was: {$msg}</p>
    </body>
</html>
HTML;
    exit;
});

require_once __DIR__ . '/vendor/autoload.php';

use Labrador\Bootstrap\FrontControllerBootstrap;

$masterConfig = include __DIR__ . '/config/master_config.php';

/** @var Auryn\Provider $provider */
/** @var Labrador\Application $app */
/** @var Symfony\Component\HttpFoundation\Request $request */
$provider = (new FrontControllerBootstrap($masterConfig))->run();
$app = $provider->make('Labrador\\Application');
$request = $provider->make('Symfony\\Component\\HttpFoundation\\Request');

$app->handle($request)->send();
