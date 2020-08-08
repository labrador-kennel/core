<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Adbar\Dot;
use Cspray\Labrador\Exception\InvalidArgumentException;

final class DotAccessSettings implements Settings {

    private $data;

    public function __construct(array $data) {
        $this->data = new Dot($data);
    }

    public function getIterator() {
        return $this->data->getIterator();
    }

    public function get(string $key) {
        if (!$this->data->has($key)) {
            throw Exceptions::createException(Exceptions::SETTINGS_ERR_KEY_NOT_FOUND, null, $key);
        }

        return $this->data->get($key);
    }

    public function has(string $key) : bool {
        return $this->data->has($key);
    }
}
