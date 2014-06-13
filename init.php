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

use Labrador\Application;
use Labrador\Bootstrap\FrontControllerBootstrap;
use Symfony\Component\HttpFoundation\Request;

$appConfig = include __DIR__ . '/config/application.php';

/** @var Auryn\Injector $provider */
/** @var Labrador\Application $app */
/** @var Symfony\Component\HttpFoundation\Request $request */
$provider = (new FrontControllerBootstrap($appConfig))->run();
$app = $provider->make(Application::class);

$app->getRouter()->get('/', 'LabradorGuide\\Controller\\HomeController#index');
$app->handle($provider->make(Request::class))->send();
