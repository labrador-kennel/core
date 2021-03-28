<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use PHPUnit\Framework\TestCase;

class StandardEnvironmentTest extends TestCase {

    public function testReturnsPassedApplicationEnvironment() {
        $subject = new StandardEnvironment(EnvironmentType::Development());

        $this->assertSame(EnvironmentType::Development(), $subject->getType());
    }

    public function testReturnsValueFromGetEnvIfNoOverride() {
        $subject = new StandardEnvironment(EnvironmentType::Development());

        $this->assertSame(getenv('USER'), $subject->getVar('USER'));
    }

    public function testReturnsValueFromOverrideIfPresent() {
        $subject = new StandardEnvironment(EnvironmentType::Development(), ['USER' => 'test-user']);

        $this->assertSame('test-user', $subject->getVar('USER'));
    }

    public function testReturnsNullIfNoValue() {
        $subject = new StandardEnvironment(EnvironmentType::Development());

        $this->assertNull($subject->getVar('FOO_BAR'));
    }
}
