<?php

/**
 * Thrown if there was an error during Labrador's bootup procedures; you cannot
 * rely on Labrador to handle this exception as it typically implies that Labrador
 * could not be started up correctly.
 * 
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Labrador\Exception;

class BootupException extends Exception {}
