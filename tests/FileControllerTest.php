<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controllers\FileController;
use App\Models\UrlShortener;

final class FileControllerTest extends TestCase
{
    private $urlShortenerMock;
    private $fileController;

    protected function setUp(): void
    {
        $this->urlShortenerMock = $this->createMock(UrlShortener::class);
        $this->fileController = new FileController();

        // Reset superglobals for each test
        $_SESSION = [];
        $_POST = [];
        $_SERVER = [];
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/f/test_hash';
    }

    public function testDownloadServesFileSuccessfully(): void
    {
        $hash = 'test_hash';
        $filePath = sys_get_temp_dir() . '/test_download_file.txt';
        $filename = 'downloaded_file.txt';
        $mimeType = 'text/plain';

        file_put_contents($filePath, 'This is a test file content.');

        $this->urlShortenerMock->method('resolve')
                               ->with($hash)
                               ->willReturn([
                                   'path' => $filePath,
                                   'filename' => $filename,
                                   'mime_type' => $mimeType
                               ]);

        // Replace the actual UrlShortener with mock for this test
        $fileController = new class($this->urlShortenerMock) extends FileController {
            private $mockUrlShortener;

            public function __construct($mockUrlShortener) {
                $this->mockUrlShortener = $mockUrlShortener;
            }

            protected function getUrlShortener(): UrlShortener {
                return $this->mockUrlShortener;
            }
        };

        $fileController = new $fileController($this->urlShortenerMock);

        // Expect headers to be sent
        $this->expectOutputString('This is a test file content.');
        $fileController->download($hash);

        unlink($filePath); // Clean up test file
    }

    public function testDownloadReturns404IfFileNotFoundInDb(): void
    {
        $hash = 'non_existent_hash';

        $this->urlShortenerMock->method('resolve')
                               ->with($hash)
                               ->willReturn(null);

        $fileController = new class() extends FileController {
            public $urlShortener;

            public function __construct($urlShortener) {
                $this->urlShortener = $urlShortener;
            }

            public function download(string $hash): void
            {
                $shortener = $this->urlShortener;
                parent::download($hash);
            }
        };

        $fileController = new $fileController($this->urlShortenerMock);

        $this->expectOutputString('File not found');
        $fileController->download($hash);
        $this->assertEquals(404, http_response_code());
    }

    public function testDownloadReturns404IfPhysicalFileDoesNotExist(): void
    {
        $hash = 'test_hash';
        $filePath = sys_get_temp_dir() . '/non_existent_physical_file.txt';
        $filename = 'downloaded_file.txt';
        $mimeType = 'text/plain';

        $this->urlShortenerMock->method('resolve')
                               ->with($hash)
                               ->willReturn([
                                   'path' => $filePath,
                                   'filename' => $filename,
                                   'mime_type' => $mimeType
                               ]);

        $fileController = new class() extends FileController {
            public $urlShortener;

            public function __construct($urlShortener) {
                $this->urlShortener = $urlShortener;
            }

            public function download(string $hash): void
            {
                $shortener = $this->urlShortener;
                parent::download($hash);
            }
        };

        $fileController = new $fileController($this->urlShortenerMock);

        $this->expectOutputString('File not found');
        $fileController->download($hash);
        $this->assertEquals(404, http_response_code());
    }
}