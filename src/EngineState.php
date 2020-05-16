<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

final class EngineState implements Enum {

    use EnumTrait;

    public static function Idle() : EngineState {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Running() : EngineState {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Crashed() : EngineState {
        return self::getSingleton(__FUNCTION__);
    }

    public function isIdling() : bool {
        return $this->equals(self::Idle());
    }

    /**
     * Return an array of enum values, that correspond to static method constructors, that are allowed for this Enum.
     *
     * @return string[]
     */
    protected static function getAllowedValues() : array {
        return ['Idle', 'Running', 'Crashed'];
    }
}
