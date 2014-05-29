<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace LabradorGuide\Controller;

use Labrador\Renderer;
use Symfony\Component\HttpFoundation\Response;

class HomeController {

    private $renderer;

    function __construct(Renderer $renderer) {
        $this->renderer = $renderer;
    }

    function index() {
        $response = $this->renderer->renderPartial('home');
        return new Response($response);
    }

} 
