<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\FileUploader;
use App\Models\StorageInterface;
use App\Models\UrlShortener;
use App\Models\CookieManager;

final class FileUploaderTest extends TestCase
{
    private $storageMock;
    private $shortenerMock;
    private $cookieManagerMock;
    private $fileUploader;

    protected function setUp(): void
    {
        $this->storageMock = $this->createMock(StorageInterface::class);
        $this->shortenerMock = $this->createMock(UrlShortener::class);
        $this->cookieManagerMock = $this->createMock(CookieManager::class);

        $this->fileUploader = new FileUploader(
            $this->storageMock,
            $this->shortenerMock,
            $this->cookieManagerMock
        );
    }

    public function testUploadSuccessfully(): void
    {
        $testFile = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php_upload_test',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        // Create a dummy temporary file for finfo to read
        file_put_contents($testFile['tmp_name'], 'dummy content');

        $this->storageMock->expects($this->once())
            ->method('save')
            ->willReturn('/path/to/stored/file.txt');

        $this->shortenerMock->expects($this->once())
            ->method('shorten')
            ->with(
                $this->equalTo('/path/to/stored/file.txt'),
                $this->equalTo('test.txt'),
                $this->equalTo('text/plain')
            )
            ->willReturn('http://localhost/f/abcdef123456');

        $this->cookieManagerMock->expects($this->once())
            ->method('addHash')
            ->with($this->equalTo('abcdef123456'));

        $shortUrl = $this->fileUploader->upload($testFile);

        $this->assertEquals('http://localhost/f/abcdef123456', $shortUrl);

        unlink($testFile['tmp_name']); // Clean up dummy file
    }

    public function testUploadThrowsExceptionOnUploadError(): void
    {
        $testFile = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php_upload_test',
            'error' => UPLOAD_ERR_INI_SIZE,
            'size' => 0,
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Upload error code: 1');

        $this->fileUploader->upload($testFile);
    }

    public function testUploadThrowsExceptionOnTooLargeFile(): void
    {
        $testFile = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php_upload_test',
            'error' => UPLOAD_ERR_OK,
            'size' => 3001 * 1024 * 1024, // Slightly larger than 3GB
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File too large (max 3 GB).');

        $this->fileUploader->upload($testFile);
    }
}