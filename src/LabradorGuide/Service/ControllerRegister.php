<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace LabradorGuide\Service;

use LabradorGuide\Controller\HomeController;
use Auryn\Injector;
use Labrador\Renderer;
use Labrador\Service\Register;

class ControllerRegister implements Register {

    private $docDir;
    private $templatesDir;

    function __construct($templatesDir, $docDir) {
        $this->templatesDir = $templatesDir;
        $this->docDir = $docDir;
    }

    function register(Injector $injector) {
        $injector->share(Renderer::class);
        $injector->define(Renderer::class, [
           ':templatesDir' => $this->templatesDir
        ]);
        $injector->share(HomeController::class);
    }

} 
