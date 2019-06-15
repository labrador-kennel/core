<?php declare(strict_types=1);

namespace Cspray\Labrador;

use function Amp\call;
use Amp\Promise;
use Cspray\Labrador\Plugin\Pluggable;
use Throwable;

/**
 *
 * @package Cspray\Labrador
 * @license See LICENSE in source root
 */
class CallbackApplication extends AbstractApplication {

    private $handler;
    private $exceptionHandler;

    public function __construct(Pluggable $pluggable, callable $executeHandler, callable $exceptionHandler = null) {
        parent::__construct($pluggable);
        $this->handler = $executeHandler;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Executes the handler passed to the constructor of this object.
     *
     * @return Promise<void>
     */
    public function execute() : Promise {
        return call($this->handler);
    }

    /**
     * Executes the exception handler, if one was provided, whenever an exception is thrown.
     *
     * @param Throwable $throwable
     * @return void
     */
    public function exceptionHandler(Throwable $throwable) : void {
        if (isset($this->exceptionHandler)) {
            ($this->exceptionHandler)($throwable);
        }
    }
}