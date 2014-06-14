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
use Labrador\ConfigDirective;
use Labrador\Bootstrap\FrontControllerBootstrap;
use Labrador\Development\Services as DevelopmentServices;
use Symfony\Component\HttpFoundation\Request;
use Configlet\MasterConfig;

$appConfig = include __DIR__ . '/config/application.php';

/** @var Auryn\Injector $injector */
/** @var Labrador\Application $app */
/** @var Symfony\Component\HttpFoundation\Request $request */
$injector = (new FrontControllerBootstrap($appConfig))->run();
$config = $injector->make(MasterConfig::class);
$app = $injector->make(Application::class);

$app->onHandle(function() use($config, $injector) {
    if ($config[ConfigDirective::ENVIRONMENT] === 'development') {
        (new DevelopmentServices($config[ConfigDirective::ROOT_DIR]  . '/.git'))->register($injector);
        $injector->make('Labrador\\Development\\HtmlToolbar')->registerEventListeners();
    }
});

$app->getRouter()->get('/', 'LabradorGuide\\Controller\\HomeController#index');

$request = Request::createFromGlobals();
$app->handle($request)->send();
