<?php declare(strict_types=1);

namespace App\Tests;

use App\Services\HomeService;
use App\Models\CookieManager;
use App\Models\UrlShortener;
use PHPUnit\Framework\TestCase;

class HomeServiceTest extends TestCase
{
    private HomeService $homeService;
    private CookieManager $cookieManager;
    private UrlShortener $urlShortener;

    protected function setUp(): void
    {
        $this->cookieManager = $this->createMock(CookieManager::class);
        $this->urlShortener = $this->createMock(UrlShortener::class);
        $this->homeService = new HomeService($this->cookieManager, $this->urlShortener);
    }

    public function testGenerateCsrfToken(): void
    {
        $token = $this->homeService->generateCsrfToken();
        
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testGetBaseHost(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['PHP_SELF'] = '/app/index.php';
        
        $baseHost = $this->homeService->getBaseHost();
        
        $this->assertEquals('https://example.com/app', $baseHost);
    }

    public function testGetBaseHostWithoutHttps(): void
    {
        unset($_SERVER['HTTPS']);
        $_SERVER['HTTP_HOST'] = 'localhost:8000';
        $_SERVER['PHP_SELF'] = '/index.php';
        
        $baseHost = $this->homeService->getBaseHost();
        
        $this->assertEquals('http://localhost:8000', $baseHost);
    }

    public function testGetUploadedFilesData(): void
    {
        $uploadedHashes = ['hash1', 'hash2', 'hash3'];
        
        $this->cookieManager->expects($this->once())
            ->method('getUploadedHashes')
            ->willReturn($uploadedHashes);
        
        $this->urlShortener->expects($this->exactly(3))
            ->method('resolve')
            ->willReturnMap([
                ['hash1', ['filename' => 'file1.txt', 'mime_type' => 'text/plain']],
                ['hash2', null],
                ['hash3', ['filename' => 'file3.jpg', 'mime_type' => 'image/jpeg']]
            ]);
        
        $result = $this->homeService->getUploadedFilesData();
        
        $expected = [
            'hash1' => [
                'filename' => 'file1.txt',
                'mime_type' => 'text/plain'
            ],
            'hash3' => [
                'filename' => 'file3.jpg',
                'mime_type' => 'image/jpeg'
            ]
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function testGetUploadedFilesDataWithNoHashes(): void
    {
        $this->cookieManager->expects($this->once())
            ->method('getUploadedHashes')
            ->willReturn([]);
        
        $this->urlShortener->expects($this->never())
            ->method('resolve');
        
        $result = $this->homeService->getUploadedFilesData();
        
        $this->assertEquals([], $result);
    }

    public function testGetHomePageData(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['PHP_SELF'] = '/index.php';
        
        $uploadedHashes = ['hash1'];
        
        $this->cookieManager->expects($this->once())
            ->method('getUploadedHashes')
            ->willReturn($uploadedHashes);
        
        $this->urlShortener->expects($this->once())
            ->method('resolve')
            ->with('hash1')
            ->willReturn(['filename' => 'test.txt', 'mime_type' => 'text/plain']);
        
        $result = $this->homeService->getHomePageData();
        
        $this->assertArrayHasKey('csrfToken', $result);
        $this->assertArrayHasKey('baseHost', $result);
        $this->assertArrayHasKey('uploadedFiles', $result);
        $this->assertEquals('https://example.com', $result['baseHost']);
        $this->assertEquals(['hash1' => ['filename' => 'test.txt', 'mime_type' => 'text/plain']], $result['uploadedFiles']);
    }
}