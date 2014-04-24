<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */ 

use Configlet\Config;

return function(Config $config) {

    $config['ini.xdebug.var_display_max_children'] = -1;
    $config['ini.xdebug.var_display_max_data'] = -1;
    $config['ini.xdebug.var_display_max_depth'] = -1;
    $config['ini.display_errors'] = true;

};
