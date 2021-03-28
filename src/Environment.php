<?php declare(strict_types=1);

namespace Cspray\Labrador;

/**
 * Provides an encapsulation over accessing environment variables set on the host OS as well as what type of application
 * environment that host system is, e.g. development vs testing vs production.
 *
 * @package Cspray\Labrador
 */
interface Environment {

    /**
     * @return EnvironmentType
     */
    public function getType() : EnvironmentType;

    /**
     *
     *
     * @param string $varName
     * @return string|int|bool|null
     */
    public function getVar(string $varName);
}
