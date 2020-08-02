<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

final class ApplicationState implements Enum {

    use EnumTrait;

    public static function Started() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Stopped() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public static function Crashed() : self {
        return self::getSingleton(__FUNCTION__);
    }

    public function isStarted() : bool {
        return $this->equals(self::Started());
    }

    public function isStopped() : bool {
        return $this->equals(self::Stopped());
    }

    public function isCrashed() : bool {
        return $this->equals(self::Crashed());
    }

    // It is imperative that if you add a new value post code generation you add the method name here!
    protected static function getAllowedValues() : array {
        return ['Started', 'Stopped', 'Crashed', ];
    }
}
