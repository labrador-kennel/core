<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\SettingsStorageHandler;

use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exception\InvalidTypeException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\SettingsStorageHandler\JsonFileSystemSettingsStorageHandler;
use Cspray\Labrador\SettingsStorageHandler\PhpFileSystemSettingsStorageHandler;
use PHPUnit\Framework\TestCase;

class JsonSettingsFileHandlerTest extends TestCase {

    public function testCanHandleFileExtensionIsJson() {
        $subject = new JsonFileSystemSettingsStorageHandler();

        $this->assertTrue($subject->canHandleSettingsPath('/path/to/file.json'));
    }

    public function testCanHandleFileExtensionIsNotJson() {
        $subject = new JsonFileSystemSettingsStorageHandler();

        $this->assertFalse($subject->canHandleSettingsPath('/path/to/file.php'));
    }

    public function testLoadSettingsFileExists() {
        $path = dirname(__DIR__) . '/resources/json_settings_file_handler_test.json';
        $subject = new JsonFileSystemSettingsStorageHandler();

        $expected = ['labrador' => ['foo' => 'bar']];
        $this->assertSame($expected, $subject->loadSettings($path));
    }

    public function testLoadSettingsFileDoesNotExist() {
        $subject = new JsonFileSystemSettingsStorageHandler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The settings path "bad path" could not be found.');

        $subject->loadSettings('bad path');
    }

    public function testLoadSettingsFileIsNotCorrectExtension() {
        $path = dirname(__DIR__) . '/resources/php_settings_file_handler_test.php';
        $subject = new JsonFileSystemSettingsStorageHandler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to load settings for path "'. $path . '". This path is unsupported by any configured SettingsStorageHandler.');
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_PATH_UNSUPPORTED);

        $subject->loadSettings($path);
    }

    public function testLoadSettingsDoesNotReturnArray() {
        $path = dirname(__DIR__) . '/resources/bad_json_settings_file_handler_test.json';
        $subject = new JsonFileSystemSettingsStorageHandler();

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('The type returned from a JSON settings file MUST be a JSON object but "' . $path . '" was parsed into a "string".');
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_JSON_INVALID_RETURN_TYPE);

        $subject->loadSettings($path);
    }
}
