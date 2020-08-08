<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Cspray\Labrador\ApplicationEnvironment;
use Cspray\Labrador\StandardEnvironment;
use PHPUnit\Framework\TestCase;

class StandardEnvironmentTest extends TestCase {

    public function testReturnsPassedApplicationEnvironment() {
        $subject = new StandardEnvironment(ApplicationEnvironment::Development());

        $this->assertSame(ApplicationEnvironment::Development(), $subject->getApplicationEnvironment());
    }

    public function testReturnsValueFromGetEnvIfNoOverride() {
        $subject = new StandardEnvironment(ApplicationEnvironment::Development());

        $this->assertSame(get_current_user(), $subject->getEnvironmentVariable('USER'));
    }

    public function testReturnsValueFromOverrideIfPresent() {
        $subject = new StandardEnvironment(ApplicationEnvironment::Development(), ['USER' => 'test-user']);

        $this->assertSame('test-user', $subject->getEnvironmentVariable('USER'));
    }

    public function testReturnsNullIfNoValue() {
        $subject = new StandardEnvironment(ApplicationEnvironment::Development());

        $this->assertNull($subject->getEnvironmentVariable('FOO_BAR'));
    }
}
