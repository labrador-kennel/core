<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController {

    function index() {
        return new JsonResponse(['msg' => 'Invoked that shit']);
    }


} 
