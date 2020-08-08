<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Cspray\Labrador\ApplicationEnvironment;

final class FileSystemEnvironmentSettingsConfiguration implements EnvironmentSettingsConfiguration {

    private $settingsPath;

    private $environmentDir;

    private $extensionsPriority;

    public function __construct(string $settingsPath, string $envDir, array $extensionsPriority = ['php', 'json']) {
        $this->settingsPath = $settingsPath;
        $this->environmentDir = $envDir;
        $this->extensionsPriority = $extensionsPriority;
    }

    public function getDefaultPath() : string {
        return $this->settingsPath;
    }

    public function getPathForApplicationEnvironment(ApplicationEnvironment $applicationEnvironment) : ?string {
        foreach ($this->extensionsPriority as $extension) {
            $path = sprintf('%s/%s.%s', $this->environmentDir, $applicationEnvironment->toString(), $extension);
            $fileInfo = new \SplFileInfo($path);
            if ($fileInfo->isFile()) {
                return $fileInfo->getPathname();
            }
        }

        return null;
    }
}