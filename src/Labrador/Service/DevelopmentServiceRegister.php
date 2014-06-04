<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Service;

use Auryn\Injector;
use Labrador\Service\Register;
use Labrador\Development\GitBranch;

class DevelopmentServiceRegister implements Register {

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
