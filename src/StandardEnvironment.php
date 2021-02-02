<?php declare(strict_types=1);

namespace Cspray\Labrador;

final class StandardEnvironment implements Environment {

    private $applicationEnvironment;
    private $envVarOverrides;

    public function __construct(EnvironmentType $applicationEnvironment, array $envVarOverrides = []) {
        $this->applicationEnvironment = $applicationEnvironment;
        $this->envVarOverrides = $envVarOverrides;
    }

    public function getType() : EnvironmentType {
        return $this->applicationEnvironment;
    }

    public function getVar(string $varName) {
        return ($this->envVarOverrides[$varName] ?? getenv($varName)) ?: null;
    }
}
