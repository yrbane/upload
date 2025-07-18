<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controllers\DeleteController;
use App\Models\UrlShortener;
use App\Models\CookieManager;

final class DeleteControllerTest extends TestCase
{
    private $urlShortenerMock;
    private $cookieManagerMock;
    private $deleteController;

    protected function setUp(): void
    {
        session_start();

        $this->urlShortenerMock = $this->createMock(UrlShortener::class);
        $this->cookieManagerMock = $this->createMock(CookieManager::class);
        $this->deleteController = new DeleteController();

        // Reset superglobals for each test
        $_SESSION = [];
        $_POST = [];
        $_SERVER = [];
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/delete';
        $_SESSION['csrf_token'] = 'test_csrf_token';
        
    }

    protected function tearDown(): void
    {
        // Clean up superglobals after each test
        $_SESSION = [];
        $_POST = [];
        $_SERVER = [];
    }

    private function mockServerVariables(string $requestUri = '/', string $requestMethod = 'GET', bool $isHttps = false, string $httpHost = 'localhost', string $phpSelf = '/index.php'): void
    {
        $_SERVER['REQUEST_URI'] = $requestUri;
        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $_SERVER['HTTPS'] = $isHttps ? 'on' : '';
        $_SERVER['HTTP_HOST'] = $httpHost;
        $_SERVER['PHP_SELF'] = $phpSelf;
    }

    public function testDeleteSuccess(): void
    {
        $this->mockServerVariables('/delete', 'POST');
        $_POST['csrf_token'] = 'test_csrf_token';
        $_POST['hash'] = 'test_hash';

        $filePath = sys_get_temp_dir() . '/test_delete_file.txt';
        file_put_contents($filePath, 'dummy content');

        $this->cookieManagerMock->method('getUploadedHashes')
                                 ->willReturn(['test_hash']);

        $this->urlShortenerMock->method('resolve')
                               ->willReturn([
                                   'path' => $filePath,
                                   'filename' => 'test.txt',
                                   'mime_type' => 'text/plain'
                               ]);

        $this->urlShortenerMock->expects($this->once())
                               ->method('deleteFile')
                               ->with('test_hash')
                               ->willReturn(true);

        $this->cookieManagerMock->expects($this->once())
                                 ->method('removeHash')
                                 ->with('test_hash');

        // Replace the actual dependencies with mocks for this test
        $deleteController = new class($this->urlShortenerMock, $this->cookieManagerMock) extends DeleteController {
            private $mockUrlShortener;
            private $mockCookieManager;

            public function __construct($mockUrlShortener, $mockCookieManager) {
                $this->mockUrlShortener = $mockUrlShortener;
                $this->mockCookieManager = $mockCookieManager;
            }

            protected function getUrlShortener(): UrlShortener {
                return $this->mockUrlShortener;
            }

            protected function getCookieManager(): CookieManager {
                return $this->mockCookieManager;
            }
        };

        ob_start();
        echo $deleteController->delete('test_hash');
        $output = ob_get_clean();

        $this->assertStringContainsString('{"success":true}', $output);
        $this->assertFileDoesNotExist($filePath);
    }

    public function testDeleteInvalidCsrfToken(): void
    {
        $this->mockServerVariables('/delete', 'POST');
        $_SESSION['csrf_token'] = 'correct_token';
        $_POST['csrf_token'] = 'wrong_token';
        $_POST['hash'] = 'test_hash';

        ob_start();
        echo $this->deleteController->delete('test_hash');
        $output = trim(ob_get_clean());

        $this->assertStringContainsString('{"error":"Invalid CSRF token."}', $output);
        $this->assertEquals(400, http_response_code());
    }

    public function testDeleteUnauthorizedHash(): void
    {
        $this->mockServerVariables('/delete', 'POST');
        $_POST['csrf_token'] = 'test_csrf_token';
        $_POST['hash'] = 'unauthorized_hash';

        $this->cookieManagerMock->method('getUploadedHashes')
                                 ->willReturn(['another_hash']);

        $deleteController = new class($this->urlShortenerMock, $this->cookieManagerMock) extends DeleteController {
            private $mockUrlShortener;
            private $mockCookieManager;

            public function __construct($mockUrlShortener, $mockCookieManager) {
                $this->mockUrlShortener = $mockUrlShortener;
                $this->mockCookieManager = $mockCookieManager;
            }

            protected function getUrlShortener(): UrlShortener {
                return $this->mockUrlShortener;
            }

            protected function getCookieManager(): CookieManager {
                return $this->mockCookieManager;
            }
        };

        ob_start();
        echo $deleteController->delete('unauthorized_hash');
        $output = ob_get_clean();

        $this->assertStringContainsString('{"error":"You are not authorized to delete this file."}', $output);
        $this->assertEquals(403, http_response_code());
    }

    public function testDeleteFileNotFoundInDatabase(): void
    {
        $this->mockServerVariables('/delete', 'POST');
        $_POST['csrf_token'] = 'test_csrf_token';
        $_POST['hash'] = 'non_existent_hash';

        $this->cookieManagerMock->method('getUploadedHashes')
                                 ->willReturn(['non_existent_hash']);

        $this->urlShortenerMock->method('resolve')
                               ->willReturn(null);

        $deleteController = new class($this->urlShortenerMock, $this->cookieManagerMock) extends DeleteController {
            private $mockUrlShortener;
            private $mockCookieManager;

            public function __construct($mockUrlShortener, $mockCookieManager) {
                $this->mockUrlShortener = $mockUrlShortener;
                $this->mockCookieManager = $mockCookieManager;
            }

            protected function getUrlShortener(): UrlShortener {
                return $this->mockUrlShortener;
            }

            protected function getCookieManager(): CookieManager {
                return $this->mockCookieManager;
            }
        };

        ob_start();
        echo $deleteController->delete('non_existent_hash');
        $output = ob_get_clean();

        $this->assertStringContainsString('{"error":"File not found in database."}', $output);
        $this->assertEquals(404, http_response_code());
    }
}