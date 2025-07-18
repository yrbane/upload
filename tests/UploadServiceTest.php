<?php declare(strict_types=1);

namespace App\Tests;

use App\Services\UploadService;
use App\Models\FileUploader;
use PHPUnit\Framework\TestCase;

class UploadServiceTest extends TestCase
{
    private UploadService $uploadService;
    private FileUploader $fileUploader;

    protected function setUp(): void
    {
        $this->fileUploader = $this->createMock(FileUploader::class);
        $this->uploadService = new UploadService($this->fileUploader);
    }

    public function testValidateCsrfTokenSuccess(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';

        $result = $this->uploadService->validateCsrfToken();
        
        $this->assertTrue($result['valid']);
        $this->assertEquals('', $result['error']);
    }

    public function testValidateCsrfTokenMissing(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        unset($_POST['csrf_token']);

        $result = $this->uploadService->validateCsrfToken();
        
        $this->assertFalse($result['valid']);
        $this->assertEquals('Token CSRF invalide', $result['error']);
    }

    public function testValidateCsrfTokenInvalid(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'invalid-token';

        $result = $this->uploadService->validateCsrfToken();
        
        $this->assertFalse($result['valid']);
        $this->assertEquals('Token CSRF invalide', $result['error']);
    }

    public function testValidateFileUploadSuccess(): void
    {
        $_FILES['file'] = [
            'name' => 'test.txt',
            'tmp_name' => 'temp',
            'error' => UPLOAD_ERR_OK,
            'size' => 1000
        ];

        $result = $this->uploadService->validateFileUpload();
        
        $this->assertTrue($result['valid']);
        $this->assertEquals('', $result['error']);
    }

    public function testValidateFileUploadMissing(): void
    {
        unset($_FILES['file']);

        $result = $this->uploadService->validateFileUpload();
        
        $this->assertFalse($result['valid']);
        $this->assertEquals('No file sent.', $result['error']);
    }

    public function testProcessUploadSuccess(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';
        $_FILES['file'] = [
            'name' => 'test.txt',
            'tmp_name' => 'temp',
            'error' => UPLOAD_ERR_OK,
            'size' => 1000
        ];

        $this->fileUploader->expects($this->once())
            ->method('upload')
            ->with($_FILES['file'])
            ->willReturn('https://example.com/f/abc123');

        $result = $this->uploadService->processUpload();
        
        $this->assertTrue($result['success']);
        $this->assertEquals('https://example.com/f/abc123', $result['url']);
        $this->assertEquals('', $result['error']);
    }

    public function testProcessUploadInvalidCsrf(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'invalid-token';
        $_FILES['file'] = [
            'name' => 'test.txt',
            'tmp_name' => 'temp',
            'error' => UPLOAD_ERR_OK,
            'size' => 1000
        ];

        $this->fileUploader->expects($this->never())
            ->method('upload');

        $result = $this->uploadService->processUpload();
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Token CSRF invalide', $result['error']);
    }

    public function testProcessUploadNoFile(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';
        unset($_FILES['file']);

        $this->fileUploader->expects($this->never())
            ->method('upload');

        $result = $this->uploadService->processUpload();
        
        $this->assertFalse($result['success']);
        $this->assertEquals('No file sent.', $result['error']);
    }

    public function testProcessUploadFileUploaderException(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';
        $_FILES['file'] = [
            'name' => 'test.txt',
            'tmp_name' => 'temp',
            'error' => UPLOAD_ERR_OK,
            'size' => 1000
        ];

        $this->fileUploader->expects($this->once())
            ->method('upload')
            ->with($_FILES['file'])
            ->willThrowException(new \RuntimeException('File too large'));

        $result = $this->uploadService->processUpload();
        
        $this->assertFalse($result['success']);
        $this->assertEquals('File too large', $result['error']);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['csrf_token']);
        unset($_POST['csrf_token']);
        unset($_FILES['file']);
    }
}