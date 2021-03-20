<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\SettingsStorageHandler;

use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidTypeException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\SettingsStorageHandler\PhpFileSystemSettingsStorageHandler;
use PHPUnit\Framework\TestCase;

class PhpSettingsFileHandlerTest extends TestCase {

    public function testCanHandleFileExtensionIsPhp() {
        $subject = new PhpFileSystemSettingsStorageHandler();

        $this->assertTrue($subject->canHandleSettingsPath('/some/path/to/file.php'));
    }

    public function testCanHandleFileExtensionIsNotPhp() {
        $subject = new PhpFileSystemSettingsStorageHandler();

        $this->assertFalse($subject->canHandleSettingsPath('/some/path/to/file.json'));
    }

    public function testLoadSettingsFileExists() {
        $path = dirname(__DIR__) . '/resources/php_settings_file_handler_test.php';
        $subject = new PhpFileSystemSettingsStorageHandler();

        $expected = ['labrador' => ['foo' => 'bar']];
        $this->assertSame($expected, $subject->loadSettings($path));
    }

    public function testLoadSettingsFileDoesNotExist() {
        $subject = new PhpFileSystemSettingsStorageHandler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The settings path "bad path" could not be found.');
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_PATH_NOT_FOUND);

        $subject->loadSettings('bad path');
    }

    public function testLoadSettingsFileIsNotCorrectExtension() {
        $path = dirname(__DIR__) . '/resources/json_settings_file_handler_test.json';
        $subject = new PhpFileSystemSettingsStorageHandler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to load settings for path "'. $path . '". This path is unsupported by any configured SettingsStorageHandler.');
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_PATH_UNSUPPORTED);

        $subject->loadSettings($path);
    }

    public function testLoadSettingsDoesNotReturnArray() {
        $path = dirname(__DIR__) . '/resources/bad_php_settings_file_handler_test.php';
        $subject = new PhpFileSystemSettingsStorageHandler();

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('The type returned from a PHP settings file MUST be an array but "' . $path . '" returned a "string".');
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_PHP_INVALID_RETURN_TYPE);

        $subject->loadSettings($path);
    }
}
