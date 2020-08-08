<?php declare(strict_types=1);

namespace Cspray\Labrador\SettingsLoader;

use Cspray\Labrador\ApplicationEnvironment;

interface EnvironmentSettingsConfiguration {

    public function getDefaultPath() : string;

    public function getPathForApplicationEnvironment(ApplicationEnvironment $applicationEnvironment) : ?string;

}