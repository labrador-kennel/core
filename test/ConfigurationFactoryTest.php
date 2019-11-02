<?php declare(strict_types=1);

namespace Cspray\Labrador\Test;

use Cspray\Labrador\ConfigurationFactory;
use Cspray\Labrador\Configuration;
use Cspray\Labrador\Exception\InvalidStateException;
use Cspray\Labrador\Test\Stub\FooPluginDependentStub;
use Cspray\Labrador\Test\Stub\FooPluginStub;
use Cspray\Labrador\Test\Stub\PluginStub;
use Cspray\Labrador\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 *
 * @package Cspray\Labrador\Test
 * @license See LICENSE in source root
 */
class ConfigurationFactoryTest extends TestCase {

    /**
     * @var ConfigurationFactory
     */
    private $subject;

    public function setUp() : void {
        parent::setUp();
        $this->subject = new ConfigurationFactory();
    }

    public function testFileDoesNotExistThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The configuration provided is not a valid file path that can be read from.');

        $this->subject->createFromFilePath('something_non_existent');
    }

    public function testFileIsNotSupportedTypeThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file extension for the provided configuration is not supported.');

        $this->subject->createFromFilePath(__DIR__ . '/_data/invalid-configuration-file-extension.txt');
    }

    public function testInvalidJsonConfigurationThrowsException() {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage("The configuration provided does not validate against the required JSON schema.");

        $this->subject->createFromFilePath(__DIR__ . '/_data/invalid-json-configuration.json');
    }

    public function testValidJsonConfigurationReturnsCorrectValues() {
        $config = $this->subject->createFromFilePath(__DIR__ . '/_data/valid-json-configuration.json');

        $this->assertSame([PluginStub::class], $config->getPlugins());
        $this->assertSame("test-env", $config->getLogName());
        $this->assertSame("php://stdout", $config->getLogPath());
        $this->assertSame('file://yadda/yadda', $config->getInjectorProviderPath());
    }

    public function testInvalidXmlConfigurationThrowsException() {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage("The configuration provided does not validate against the required XML schema.");

        $this->subject->createFromFilePath(__DIR__ . '/_data/invalid-xml-configuration.xml');
    }

    public function testValidXmlConfigurationReturnsCorrectValues() {
        $config = $this->subject->createFromFilePath(__DIR__ . '/_data/valid-xml-configuration.xml');

        $this->assertSame("xml-config-log", $config->getLogName());
        $this->assertSame("php://stdout", $config->getLogPath());
        $this->assertSame([PluginStub::class], $config->getPlugins());
        $this->assertSame('file://yadda/yadda/xml', $config->getInjectorProviderPath());
    }

    public function testInvalidPhpReturnTypeThrowsException() {
        $this->expectException(InvalidStateException::class);
        $msg = 'The configuration provided does not return a valid PHP array or ' . Configuration::class . ' instance';
        $this->expectExceptionMessage($msg);

        $this->subject->createFromFilePath(__DIR__ . '/_data/invalid-return-type-php-configuration.php');
    }

    public function testInvalidPhpArraySchemaThrowsException() {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage("The configuration provided does not validate against the required JSON schema.");

        $this->subject->createFromFilePath(__DIR__ . '/_data/invalid-schema-php-configuration.php');
    }

    public function testValidPhpArraySchemaHasCorrectData() {
        $config = $this->subject->createFromFilePath(__DIR__ . '/_data/valid-array-php-configuration.php');

        $this->assertSame('php-array-log', $config->getLogName());
        $this->assertSame('php://stdout', $config->getLogPath());
        $this->assertSame([PluginStub::class, FooPluginDependentStub::class], $config->getPlugins());
        $this->assertSame('file://yadda/yadda/array', $config->getInjectorProviderPath());
    }

    public function testValidPhpConfigurationHasCorrectData() {
        $path = __DIR__ . '/_data/valid-configuration-instance-php-configuration.php';
        $config = $this->subject->createFromFilePath($path);

        $this->assertSame('php-instance-log', $config->getLogName());
        $this->assertSame('php://stdout', $config->getLogPath());
        $this->assertSame('file://yadda/yadda/instance', $config->getInjectorProviderPath());
        $this->assertSame([PluginStub::class, FooPluginStub::class], $config->getPlugins());
    }
}
