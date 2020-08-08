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
final class ApplicationEnvironment implements Enum {

    use EnumTrait;

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
