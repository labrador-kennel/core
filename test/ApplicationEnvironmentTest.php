<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Cspray\Labrador\EnvironmentType;
use PHPUnit\Framework\TestCase;

class ApplicationEnvironmentTest extends TestCase {

    public function testValueOfIsCaseInsensitive() {
        $dev = EnvironmentType::valueOf('development');
        $this->assertSame(EnvironmentType::Development(), $dev);
    }
}
