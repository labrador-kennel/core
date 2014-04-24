<?php

/**
 * 
 * @author  Charles Sprayberry
 * @license See LICENSE in source root
 * @version 0.1
 * @since   0.1
 */

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
