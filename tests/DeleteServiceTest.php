<?php declare(strict_types=1);

namespace App\Tests;

use App\Services\DeleteService;
use App\Models\UrlShortener;
use App\Models\CookieManager;
use PHPUnit\Framework\TestCase;

class DeleteServiceTest extends TestCase
{
    private DeleteService $deleteService;
    private UrlShortener $urlShortener;
    private CookieManager $cookieManager;

    protected function setUp(): void
    {
        $this->urlShortener = $this->createMock(UrlShortener::class);
        $this->cookieManager = $this->createMock(CookieManager::class);
        $this->deleteService = new DeleteService($this->urlShortener, $this->cookieManager);
    }

    public function testValidateCsrfTokenSuccess(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';

        $result = $this->deleteService->validateCsrfToken();
        
        $this->assertTrue($result['valid']);
        $this->assertEquals('', $result['error']);
    }

    public function testValidateCsrfTokenInvalid(): void
    {
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'invalid-token';

        $result = $this->deleteService->validateCsrfToken();
        
        $this->assertFalse($result['valid']);
        $this->assertEquals('Invalid CSRF token.', $result['error']);
    }

    public function testValidateUserAuthorizationSuccess(): void
    {
        $hash = 'abc123';
        $uploadedHashes = ['abc123', 'def456'];

        $this->cookieManager->expects($this->once())
            ->method('getUploadedHashes')
            ->willReturn($uploadedHashes);

        $result = $this->deleteService->validateUserAuthorization($hash);
        
        $this->assertTrue($result['valid']);
        $this->assertEquals('', $result['error']);
    }

    public function testValidateUserAuthorizationFailed(): void
    {
        $hash = 'abc123';
        $uploadedHashes = ['def456', 'ghi789'];

        $this->cookieManager->expects($this->once())
            ->method('getUploadedHashes')
            ->willReturn($uploadedHashes);

        $result = $this->deleteService->validateUserAuthorization($hash);
        
        $this->assertFalse($result['valid']);
        $this->assertEquals('You are not authorized to delete this file.', $result['error']);
    }

    public function testDeleteFileSuccess(): void
    {
        $hash = 'abc123';
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'test content');

        $fileData = [
            'path' => $tempFile,
            'filename' => 'test.txt'
        ];

        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';

        $this->cookieManager->expects($this->once())
            ->method('getUploadedHashes')
            ->willReturn(['abc123']);

        $this->urlShortener->expects($this->once())
            ->method('resolve')
            ->with($hash)
            ->willReturn($fileData);

        $this->urlShortener->expects($this->once())
            ->method('deleteFile')
            ->with($hash);

        $this->cookieManager->expects($this->once())
            ->method('removeHash')
            ->with($hash);

        $result = $this->deleteService->deleteFile($hash);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('', $result['error']);
        $this->assertFalse(file_exists($tempFile));
    }

    public function testDeleteFileInvalidCsrf(): void
    {
        $hash = 'abc123';
        
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'invalid-token';

        $this->cookieManager->expects($this->never())
            ->method('getUploadedHashes');

        $result = $this->deleteService->deleteFile($hash);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid CSRF token.', $result['error']);
    }

    public function testDeleteFileUnauthorized(): void
    {
        $hash = 'abc123';
        
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';

        $this->cookieManager->expects($this->once())
            ->method('getUploadedHashes')
            ->willReturn(['def456']);

        $result = $this->deleteService->deleteFile($hash);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('You are not authorized to delete this file.', $result['error']);
    }

    public function testDeleteFileNotFound(): void
    {
        $hash = 'abc123';
        
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';

        $this->cookieManager->expects($this->once())
            ->method('getUploadedHashes')
            ->willReturn(['abc123']);

        $this->urlShortener->expects($this->once())
            ->method('resolve')
            ->with($hash)
            ->willReturn(null);

        $result = $this->deleteService->deleteFile($hash);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('File not found in database.', $result['error']);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['csrf_token']);
        unset($_POST['csrf_token']);
    }
}