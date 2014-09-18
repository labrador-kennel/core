<?php

/**
 * The set of configuration directives that Labrador will respond to.
 * 
 * @license See LICENSE in source root
 */

namespace Labrador;

abstract class ConfigDirective {

    const ENVIRONMENT = 'labrador.environment';
    const ROOT_DIR = 'labrador.root_dir';
    const BOOTSTRAP_CALLBACK = 'labrador.bootstraps_callback';

} 
