<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AbstractApplication;
use Cspray\Labrador\Plugin\Pluggable;
use Throwable;
use function Amp\async;

class TestApplication extends AbstractApplication {

    private $closure;
    private $exceptionHandler;

    public function __construct(Pluggable $pluggable, callable $executeHandler, callable $exceptionHandler = null) {
        parent::__construct($pluggable);
        $this->closure = $executeHandler;
        $this->exceptionHandler = $exceptionHandler;
    }

    protected function doStart() : Future {
        return async($this->closure);
    }

    public function handleException(Throwable $throwable) : void {
        if (isset($this->exceptionHandler)) {
            ($this->exceptionHandler)($throwable);
        }
    }
}
