<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\Stub;

use Cspray\Labrador\SettingsStorageHandler;

class SettingsStorageHandlerStub implements SettingsStorageHandler {

    private $data;
    private $path;

    private $loadedFilePath = null;

    public function __construct(string $path, array $data) {
        $this->path = $path;
        $this->data = $data;
    }

    public function getLoadedFilePath() : ?string {
        return $this->loadedFilePath;
    }

    public function canHandleSettingsPath(string $path) : bool {
        return $this->path === $path;
    }

    public function loadSettings(string $filePath) : array {
        $this->loadedFilePath = $filePath;
        return $this->data;
    }
}
