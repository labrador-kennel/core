<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Development;

use Auryn\Injector;
use Labrador\Service\Register;

class Services implements Register {

    private $gitDir;

    function __construct($gitDir) {
        $this->gitDir = $gitDir;
    }

    function register(Injector $injector) {
        $injector->share(GitBranch::class);
        $injector->define(GitBranch::class, [
            ':gitDir' => $this->gitDir
        ]);
    }

} 
