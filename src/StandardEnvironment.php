<?php declare(strict_types=1);

namespace Cspray\Labrador;

final class StandardEnvironment implements Environment {

    private $applicationEnvironment;
    private $envVarOverrides;

    public function __construct(ApplicationEnvironment $applicationEnvironment, array $envVarOverrides = []) {
        $this->applicationEnvironment = $applicationEnvironment;
        $this->envVarOverrides = $envVarOverrides;
    }

    public function getApplicationEnvironment() : ApplicationEnvironment {
        return $this->applicationEnvironment;
    }

    public function getEnvironmentVariable(string $varName) {
        return ($this->envVarOverrides[$varName] ?? getenv($varName)) ?: null;
    }
}
