<?php

namespace Labrador\Collection;

use Labrador\Exception\UnsupportedOperationException;

class ImmutableSafeHashMap extends SafeHashMap {

    public function __construct(array $data, callable $hashingFunc = null) {
        parent::__construct($hashingFunc);
        $this->setInitialData($data);
    }

    private function setInitialData(array $data) {
        foreach ($data as $k => $v) {
            parent::offsetSet($k, $v);
        }
    }

    public function offsetSet($key, $val) {
        $msg = "You may not alter the attributes of a %s after instance creation.";
        throw new UnsupportedOperationException(sprintf($msg, self::class));
    }

    public function offsetUnset($key) {
        $msg = 'You may not destroy the attributes of a %s after instance creation.';
        throw new UnsupportedOperationException(sprintf($msg, self::class));
    }

}