<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Yape\Enum;

final class EngineState implements Enum {

    private static $container = [];

    private $enumConstName;
    private $value;

    private function __construct(string $enumConstName, string $value) {
        $this->enumConstName = $enumConstName;
        $this->value = $value;
    }

    protected static function getSingleton($value, ...$additionalConstructorArgs) {
        if (!isset(self::$container[$value])) {
            self::$container[$value] = new self(...array_merge([$value], $additionalConstructorArgs));
        }

        return self::$container[$value];
    }

    public static function Idle() : EngineState {
        return self::getSingleton('Idle', 'idle');
    }

    public static function Running() : EngineState {
        return self::getSingleton('Running', 'running');
    }

    public static function Crashed() : EngineState {
        return self::getSingleton('Crashed', 'crashed');
    }

    public function getValue() : string {
        return $this->value;
    }

    public function isIdling() : bool  {
        return $this->equals(EngineState::Idle());
    }

    public function equals(EngineState $engineState) : bool {
        return $this === $engineState;
    }

    public function toString() : string {
        return get_class($this) . '@' . $this->enumConstName;
    }
}
