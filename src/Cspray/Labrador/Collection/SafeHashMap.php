<?php

declare(strict_types=1);

/**
 * A HashMap that will return null if the key requested was not found.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\Labrador\Collection;

use Collections\HashMap;

class SafeHashMap extends HashMap {

    /**
     * @param mixed $key
     * @return mixed|null
     * @throws \Collections\KeyException
     */
    public function offsetGet($key) {
        if (!isset($this[$key])) {
            return null;
        }

        return parent::offsetGet($key);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key) {
        return $this->offsetGet($key);
    }

}
