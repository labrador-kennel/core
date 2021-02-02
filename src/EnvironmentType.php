<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

/**
 * An Enum that represents what host environment your application is running on.
 *
 * @package Cspray\Labrador
 * @see Environment
 */
final class EnvironmentType implements Enum {

    use EnumTrait {
        valueOf as strictValueOf;
    }

    public static function Development() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Test() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Staging() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Production() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public function toString() : string {
        return strtolower($this->enumValue);
    }

    public static function valueOf(string $value) : self {
        $normalizedValue = ucfirst(strtolower($value));
        return self::strictValueOf($normalizedValue);
    }

    // It is imperative that if you add a new value post code generation you add the method name here!
    protected static function getAllowedValues() : array {
        return [
            'Development',
            'Test',
            'Staging',
            'Production',
        ];
    }
}
