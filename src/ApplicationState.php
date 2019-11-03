<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

final class ApplicationState implements Enum {

    use EnumTrait;

    public static function Started() : self {
        return self::getSingleton('Started');
    }

    public static function Stopped() : self {
        return self::getSingleton('Stopped');
    }

    public static function Crashed() : self {
        return self::getSingleton('Crashed');
    }

    // It is imperative that if you add a new value post code generation you add the method name here!
    protected static function getAllowedValues() : array {
        return ['Started', 'Stopped', 'Crashed', ];
    }
}
