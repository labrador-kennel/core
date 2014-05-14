<?php

use Labrador\Router\Router;

return function(Router $router) {

    $router->get('/', 'LabradorDemo\\Controller\\HomeController#index');
    $router->get('/user-guide', 'LabradorDemo\\Controller\\HomeController#userGuide');

};
