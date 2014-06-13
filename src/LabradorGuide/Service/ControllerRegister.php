<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace LabradorGuide\Service;

use Configlet\Config;
use Labrador\ConfigDirective;
use LabradorGuide\Controller\HomeController;
use Labrador\Renderer;
use Labrador\Service\Register;
use Auryn\Injector;

class ControllerRegister implements Register {

    private $templatesDir;

    function __construct(Config $config) {
        $this->templatesDir = $config[ConfigDirective::ROOT_DIR] . '/src/LabradorGuide/_templates';
    }

    function register(Injector $injector) {
        $injector->share(Renderer::class);
        $injector->define(Renderer::class, [
            ':templatesDir' => $this->templatesDir
        ]);
        $injector->share(HomeController::class);
    }

}
