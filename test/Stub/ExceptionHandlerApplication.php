<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Amp\Promise;
use Amp\Success;
use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\Emitter;
use Cspray\Labrador\Plugin\Pluggable;
use Throwable;

class ExceptionHandlerApplication extends CallbackApplication {

    private $handler;

    public function __construct(callable $appCallback, callable $handler) {
        parent::__construct($appCallback);
        $this->handler = $handler;
    }

    public function exceptionHandler(Throwable $throwable) : void {
        $handler = $this->handler;
        $handler($throwable);
    }

}