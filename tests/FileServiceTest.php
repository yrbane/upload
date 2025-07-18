<?php declare(strict_types=1);

namespace App\Tests;

use App\Services\FileService;
use App\Services\LocalizationService;
use App\Models\UrlShortener;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{
    private FileService $fileService;
    private UrlShortener $urlShortener;
    private LocalizationService $localizationService;

    protected function setUp(): void
    {
        $this->urlShortener = $this->createMock(UrlShortener::class);
        $this->localizationService = $this->createMock(LocalizationService::class);
        $this->fileService = new FileService($this->urlShortener, $this->localizationService);
    }

    public function testGetFileDataSuccess(): void
    {
        $hash = 'abc123';
        $fileData = [
            'path' => '/tmp/test.txt',
            'filename' => 'test.txt',
            'mime_type' => 'text/plain'
        ];

        $this->urlShortener->expects($this->once())
            ->method('resolve')
            ->with($hash)
            ->willReturn($fileData);

        $result = $this->fileService->getFileData($hash);

        $this->assertEquals($fileData, $result);
    }

    public function testGetFileDataNotFound(): void
    {
        $hash = 'nonexistent';

        $this->urlShortener->expects($this->once())
            ->method('resolve')
            ->with($hash)
            ->willReturn(null);

        $result = $this->fileService->getFileData($hash);

        $this->assertNull($result);
    }

    public function testFileExistsTrue(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'test content');

        $result = $this->fileService->fileExists($tempFile);

        $this->assertTrue($result);

        unlink($tempFile);
    }

    public function testFileExistsFalse(): void
    {
        $result = $this->fileService->fileExists('/nonexistent/file.txt');

        $this->assertFalse($result);
    }

    public function testGetFileContent(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        $content = 'test file content';
        file_put_contents($tempFile, $content);

        $result = $this->fileService->getFileContent($tempFile);

        $this->assertEquals($content, $result);

        unlink($tempFile);
    }

    public function testPrepareFileForDownload(): void
    {
        $hash = 'abc123';
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        $content = 'test file content';
        file_put_contents($tempFile, $content);

        $fileData = [
            'path' => $tempFile,
            'filename' => 'test.txt',
            'mime_type' => 'text/plain'
        ];

        $this->urlShortener->expects($this->once())
            ->method('resolve')
            ->with($hash)
            ->willReturn($fileData);

        $result = $this->fileService->prepareFileForDownload($hash);

        $this->assertTrue($result['success']);
        $this->assertEquals($content, $result['content']);
        $this->assertEquals('text/plain', $result['mimeType']);
        $this->assertEquals('test.txt', $result['filename']);
        $this->assertEquals('', $result['error']);

        unlink($tempFile);
    }

    public function testPrepareFileForDownloadFileNotFound(): void
    {
        $hash = 'nonexistent';

        $this->urlShortener->expects($this->once())
            ->method('resolve')
            ->with($hash)
            ->willReturn(null);

        $this->localizationService->expects($this->once())
            ->method('translate')
            ->with('error.file_not_found')
            ->willReturn('File not found');

        $result = $this->fileService->prepareFileForDownload($hash);

        $this->assertFalse($result['success']);
        $this->assertEquals('File not found', $result['error']);
    }

    public function testPrepareFileForDownloadPhysicalFileNotFound(): void
    {
        $hash = 'abc123';
        $fileData = [
            'path' => '/nonexistent/file.txt',
            'filename' => 'test.txt',
            'mime_type' => 'text/plain'
        ];

        $this->urlShortener->expects($this->once())
            ->method('resolve')
            ->with($hash)
            ->willReturn($fileData);

        $this->localizationService->expects($this->once())
            ->method('translate')
            ->with('error.file_not_found')
            ->willReturn('File not found');

        $result = $this->fileService->prepareFileForDownload($hash);

        $this->assertFalse($result['success']);
        $this->assertEquals('File not found', $result['error']);
    }
}