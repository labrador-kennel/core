<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace LabradorGuide\Controller;

use Symfony\Component\HttpFoundation\Response;

class HomeController {

    private $templatesDir;
    private $docDir;

    function __construct($templatesDir, $docDir) {
        $this->templatesDir = $templatesDir;
        $this->docDir = $docDir;
    }

    function index() {
        $response = file_get_contents($this->templatesDir . '/home.php');
        return new Response($response);
    }

} 
