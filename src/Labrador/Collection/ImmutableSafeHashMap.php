<?php

/**
 * A SafeHashMap implementation that does not allow data to be modified
 * after the map has been created.
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Collection;

use Labrador\Exception\UnsupportedOperationException;

class ImmutableSafeHashMap extends SafeHashMap {

    /**
     * @param array $data
     * @param callable $hashingFunc
     */
    public function __construct(array $data, callable $hashingFunc = null) {
        parent::__construct($hashingFunc);
        $this->setInitialData($data);
    }

    /**
     * @param array $data
     */
    private function setInitialData(array $data) {
        foreach ($data as $k => $v) {
            // we are intentionally calling parent::offsetSet here so
            // that we do not throw an exception calling $this->offsetSet
            parent::offsetSet($k, $v);
        }
    }

    /**
     * @param mixed $key
     * @param mixed $val
     * @throws UnsupportedOperationException
     */
    public function offsetSet($key, $val) {
        $msg = "You may not alter the attributes of a %s after instance creation.";
        throw new UnsupportedOperationException(sprintf($msg, self::class));
    }

    /**
     * @param mixed $key
     * @throws UnsupportedOperationException
     */
    public function offsetUnset($key) {
        $msg = 'You may not destroy the attributes of a %s after instance creation.';
        throw new UnsupportedOperationException(sprintf($msg, self::class));
    }

}