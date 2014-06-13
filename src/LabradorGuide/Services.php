<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace LabradorGuide;

use LabradorGuide\Controller\HomeController;
use Labrador\Renderer;
use Labrador\Service\Register;
use Auryn\Injector;

class Services implements Register {

    private $templatesDir;

    function __construct() {
        $this->templatesDir = __DIR__ . '/_templates';
    }

    function register(Injector $injector) {
        $injector->share(Renderer::class);
        $injector->define(Renderer::class, [
            ':templatesDir' => $this->templatesDir
        ]);
        $injector->share(HomeController::class);
    }

}
