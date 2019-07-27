<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * @package Cspray\Labrador\Test\Stub
 * @license See LICENSE in source root
 */
class LoggerAwareStub implements LoggerAwareInterface {

    public $logger;

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
