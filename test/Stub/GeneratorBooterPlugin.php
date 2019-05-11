<?php declare(strict_types = 1);


namespace Cspray\Labrador\Test\Stub;

use Amp\Delayed;
use Cspray\Labrador\Plugin\BootablePlugin;

class GeneratorBooterPlugin implements BootablePlugin {

    private $timesYielded = 0;

    public function boot(): callable {
        return function() {
            yield new Delayed(1);
            $this->timesYielded++;
            yield new Delayed(1);
            $this->timesYielded++;
            yield new Delayed(1);
            $this->timesYielded++;
        };
    }

    public function getTimesYielded() : int {
        return $this->timesYielded;
    }
}
