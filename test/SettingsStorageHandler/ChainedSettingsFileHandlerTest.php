<?php declare(strict_types=1);

namespace Cspray\Labrador\Test\SettingsStorageHandler;

use Cspray\Labrador\Exception\InvalidArgumentException;
use Cspray\Labrador\Exceptions;
use Cspray\Labrador\SettingsStorageHandler\ChainedSettingsStorageHandler;
use Cspray\Labrador\Test\Stub\SettingsStorageHandlerStub;
use PHPUnit\Framework\TestCase;

class ChainedSettingsFileHandlerTest extends TestCase {

    private $subject;

    private $fooFileHandler;
    private $bazFileHandler;
    private $barFileHandler;

    public function setUp() : void {
        parent::setUp();
        $this->fooFileHandler = new SettingsStorageHandlerStub('/path/to/file.foo', ['foo']);
        $this->barFileHandler = new SettingsStorageHandlerStub('/path/to/file.bar', ['bar']);
        $this->bazFileHandler = new SettingsStorageHandlerStub('/path/to/file.baz', ['baz']);

        $this->subject = new ChainedSettingsStorageHandler(
            $this->fooFileHandler,
            $this->barFileHandler,
            $this->bazFileHandler
        );
    }

    public function testCanHandleExtensionWithNoFileHandlers() {
        $this->assertFalse($this->subject->canHandleSettingsPath('/path/to/file.php'));
    }

    public function testCanHandleExtensionWithFileHandlers() {
        $this->assertTrue($this->subject->canHandleSettingsPath('/path/to/file.bar'));
    }

    public function testLoadingFileWithGoodExtension() {
        $fooPath = '/path/to/file.foo';

        $this->assertSame(['foo'], $this->subject->loadSettings($fooPath));
        $this->assertSame($fooPath, $this->fooFileHandler->getLoadedFilePath());
    }

    public function testLoadingFileWithBadExtension() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unable to load settings for path "/bad/path/to/foo.qux". ' .
            'This path is unsupported by any configured SettingsStorageHandler.'
        );
        $this->expectExceptionCode(Exceptions::SETTINGS_ERR_PATH_UNSUPPORTED);

        $this->subject->loadSettings('/bad/path/to/foo.qux');
    }

    public function testAddingFileHandlerAfterConstructions() {
        $path = '/path/to/foo.qux';
        $quxHandler = new SettingsStorageHandlerStub('/path/to/foo.qux', ['qux']);
        $this->subject->addSettingsFileHandler($quxHandler);

        $this->assertSame(['qux'], $this->subject->loadSettings($path));
        $this->assertSame($path, $quxHandler->getLoadedFilePath());
    }
}
