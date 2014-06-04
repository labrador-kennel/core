<?php

/**
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Development;


class GitBranch {

    private $gitDir;

    function __construct($gitDir) {
        $this->gitDir = $gitDir;
    }

    function getBranchName() {
        $ref = file_get_contents($this->gitDir . '/HEAD');
        $branch = preg_replace('#^ref: refs/heads/#', '', $ref);
        $branch = trim($branch, ' ' . PHP_EOL);
        return $branch;
    }

} 
