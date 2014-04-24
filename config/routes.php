<?php

use Labrador\Router\Router;

return function(Router $router) {

    $router->get('/', 'Labrador\\Controller\\HomeController#index');

};
