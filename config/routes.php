<?php

use Labrador\Router\Router;

return function(Router $router) {

    $router->get('/', 'LabradorGuide\\Controller\\HomeController#index');

};
