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
     * @return ApplicationEnvironment
     */
    public function getApplicationEnvironment() : ApplicationEnvironment;

    /**
     *
     *
     * @param string $varName
     * @return string|int|bool|null
     */
    public function getEnvironmentVariable(string $varName);
}
