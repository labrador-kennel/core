<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace LabradorDemo\Service;

use Auryn\Injector;
use Labrador\Service\Register;

class ControllerRegister implements Register {

    private $docDir;
    private $templatesDir;

    function __construct($templatesDir, $docDir) {
        $this->templatesDir = $templatesDir;
        $this->docDir = $docDir;
    }

    function register(Injector $injector) {
        $injector->share('LabradorDemo\\Controller\\HomeController');
        $injector->define('LabradorDemo\\Controller\\HomeController', [
            ':templatesDir' => $this->templatesDir,
            ':docDir' => $this->docDir
        ]);
    }

} 
