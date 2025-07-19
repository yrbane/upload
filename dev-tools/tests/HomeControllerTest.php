<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controllers\HomeController;
use App\Models\CookieManager;
use App\Models\UrlShortener;

final class HomeControllerTest extends TestCase
{
    private $cookieManagerMock;
    private $urlShortenerMock;
    private $homeController;

    protected function setUp(): void
    {
        session_start();

        // Mock dependencies
        $this->cookieManagerMock = $this->createMock(CookieManager::class);
        $this->urlShortenerMock = $this->createMock(UrlShortener::class);

        // Inject mocks into the controller (using reflection for private properties if necessary, or a setter)
        // For simplicity, we'll directly instantiate and rely on the constructor if it takes dependencies.
        // Since HomeController doesn't take them in constructor, we'll mock the global state it relies on.

        // Reset $_SESSION for each test
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/';

        // Mock the UrlShortener constructor behavior for HomeController
        // This is a bit tricky as UrlShortener is instantiated inside HomeController::index()
        // For now, we'll assume UrlShortener works as expected and focus on HomeController's logic.
        // A more advanced testing setup might involve a dependency injection container.

        $this->homeController = new HomeController();
    }

    protected function tearDown(): void
    {
        $_SESSION = []; // Clean up session after each test
    }

    public function testIndexGeneratesCsrfToken(): void
    {
        $this->homeController->index();
        $this->assertArrayHasKey('csrf_token', $_SESSION);
        $this->assertNotEmpty($_SESSION['csrf_token']);
    }

    public function testIndexLoadsUploadedFiles(): void
    {
        // Mock CookieManager behavior
        $this->cookieManagerMock->method('getUploadedHashes')
                                ->willReturn(['hash1', 'hash2']);

        // Mock UrlShortener behavior
        $this->urlShortenerMock->method('resolve')
                               ->willReturnMap([
                                   ['hash1', ['path' => '/path/1', 'filename' => 'file1.txt', 'mime_type' => 'text/plain']],
                                   ['hash2', ['path' => '/path/2', 'filename' => 'file2.jpg', 'mime_type' => 'image/jpeg']],
                               ]);

        // Create a mock HomeService
        $homeService = $this->createMock(\App\Services\HomeService::class);
        $homeService->method('getHomePageData')
                    ->willReturn([
                        'csrfToken' => 'test-token',
                        'baseHost' => 'http://localhost',
                        'uploadedFiles' => [
                            'hash1' => ['filename' => 'file1.txt', 'mime_type' => 'text/plain'],
                            'hash2' => ['filename' => 'file2.jpg', 'mime_type' => 'image/jpeg']
                        ]
                    ]);

        // Inject the mock service
        $homeController = new \App\Controllers\HomeController($homeService);

        // Get the response and check its content
        $response = $homeController->index();
        
        $this->assertInstanceOf(\App\Http\Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['Content-Type' => 'text/html'], $response->getHeaders());
        
        $output = $response->getContent();
        $this->assertStringContainsString('file1.txt', $output);
        $this->assertStringContainsString('file2.jpg', $output);
        $this->assertStringContainsString('hash1', $output);
        $this->assertStringContainsString('hash2', $output);
    }
}