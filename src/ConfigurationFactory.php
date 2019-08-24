<?php declare(strict_types=1);

namespace Cspray\Labrador;

use Ds\Set;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use DOMDocument;
use stdClass;

/**
 *
 *
 * @package Cspray\Labrador\Configuration
 * @license See LICENSE in source root
 */
class ConfigurationFactory {

    private $schemasDirectory;

    public function __construct() {
        $this->schemasDirectory = dirname(__DIR__, 1) . '/resources/schemas';
    }

    public function createFromFilePath(string $configurationPath) : Configuration {
        if (!file_exists($configurationPath)) {
            throw Exceptions::createException(Exceptions::CONFIG_ERR_FILE_NOT_EXIST);
        }
        $extension = pathinfo($configurationPath, PATHINFO_EXTENSION);
        if ($extension === 'json') {
            $configData = $this->loadJsonConfigData($configurationPath);
        } elseif ($extension === 'xml') {
            $configData = $this->loadXmlConfigData($configurationPath);
        } elseif ($extension === 'php') {
            $configData = $this->loadPhpConfigData($configurationPath);
        } else {
            throw Exceptions::createException(Exceptions::CONFIG_ERR_FILE_UNSUPPORTED_EXTENSION);
        }

        return $this->createConfiguration($configData);
    }

    private function loadJsonConfigData(string $configurationPath) : stdClass {
        return $this->validateJsonSource(file_get_contents($configurationPath));
    }

    private function loadXmlConfigData(string $configurationPath) : stdClass {
        $xmlSchemaPath = $this->schemasDirectory . '/configuration.schema.xsd';
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadXML(file_get_contents($configurationPath));

        if (!empty(libxml_get_errors()) || !$dom->schemaValidate($xmlSchemaPath)) {
            $exception = Exceptions::createException(Exceptions::CONFIG_ERR_XML_INVALID_SCHEMA);
            throw $exception;
        }

        libxml_clear_errors();
        libxml_use_internal_errors(false);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('l', 'https://labrador-kennel.io/core/schemas/configuration.schema.xsd');

        $configData = new stdClass();

        $configData->labrador = new stdClass();
        $configData->labrador->logging = new stdClass();
        $configData->labrador->logging->name = $xpath->evaluate('/l:labrador/l:logging/l:name/text()')
                                                    ->item(0)->nodeValue;
        $configData->labrador->logging->outputPath = $xpath->evaluate('/l:labrador/l:logging/l:outputPath/text()')
                                                            ->item(0)->nodeValue;

        $configData->labrador->plugins = new Set();
        foreach ($xpath->query('/l:labrador/l:plugins/l:plugin') as $pluginNode) {
            $configData->labrador->plugins->add($pluginNode->nodeValue);
        }

        $configData->labrador->injectorProviderPath = $xpath->evaluate('/l:labrador/l:injectorProviderPath/text()')
                                                            ->item(0)->nodeValue;

        return $configData;
    }

    private function loadPhpConfigData(string $configurationPath) : stdClass {
        $phpConfig = include $configurationPath;
        if (is_array($phpConfig)) {
            return $this->validateJsonSource(json_encode($phpConfig));
        } elseif (is_object($phpConfig) && $phpConfig instanceof Configuration) {
            $configData = new stdClass();
            $configData->configuration = $phpConfig;
            return $configData;
        } else {
            throw Exceptions::createException(Exceptions::CONFIG_ERR_PHP_INVALID_RETURN_TYPE);
        }
    }

    private function validateJsonSource(string $jsonSource) : stdClass {
        $jsonSchemaPath = $this->schemasDirectory . '/configuration.schema.json';
        $jsonSchema = Schema::fromJsonString(file_get_contents($jsonSchemaPath));
        $jsonValidator = new Validator();
        $configData = json_decode($jsonSource);

        $results = $jsonValidator->schemaValidation($configData, $jsonSchema);

        if (!$results->isValid()) {
            $exception = Exceptions::createException(Exceptions::CONFIG_ERR_JSON_INVALID_SCHEMA);
            throw $exception;
        }

        $configData->labrador->plugins = new Set($configData->labrador->plugins);

        return $configData;
    }

    private function createConfiguration(stdClass $configData) {
        if (isset($configData->configuration)) {
            return $configData->configuration;
        }

        $logName = $configData->labrador->logging->name;
        $logPath = $configData->labrador->logging->outputPath;
        $plugins = $configData->labrador->plugins;
        $injectorProviderPath = $configData->labrador->injectorProviderPath;

        return new class(
            $logName,
            $logPath,
            $plugins,
            $injectorProviderPath
        ) implements Configuration {

            private $logName;
            private $logPath;
            private $plugins;
            private $injectorProviderPath;

            public function __construct(
                string $logName,
                string $logPath,
                Set $plugins,
                string $injectorProviderPath
            ) {
                $this->logName = $logName;
                $this->logPath = $logPath;
                $this->plugins = $plugins;
                $this->injectorProviderPath = $injectorProviderPath;
            }

            public function getLogName() : string {
                return $this->logName;
            }

            public function getLogPath() : string {
                return $this->logPath;
            }

            public function getPlugins() : Set {
                return $this->plugins;
            }

            public function getInjectorProviderPath() : string {
                return $this->injectorProviderPath;
            }
        };
    }
}
