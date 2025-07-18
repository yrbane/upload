<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Controllers\UploadController;
use App\Models\FileUploader;

final class UploadControllerTest extends TestCase
{
    private $fileUploaderMock;
    private $uploadController;

    protected function setUp(): void
    {
        $this->fileUploaderMock = $this->createMock(FileUploader::class);
        $this->uploadController = new UploadController();

        // Reset superglobals for each test
        $_SESSION = [];
        $_POST = [];
        $_FILES = [];
        $_SERVER = [];
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/upload';
        $_SESSION['csrf_token'] = 'test_csrf_token';
        session_start();
    }

    protected function tearDown(): void
    {
        // Clean up superglobals after each test
        $_SESSION = [];
        $_POST = [];
        $_FILES = [];
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

    public function testUploadSuccess(): void
    {
        $this->mockServerVariables('/upload', 'POST');
        $_POST['csrf_token'] = 'test_csrf_token';
        $_FILES['file'] = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php_upload_test',
            'error' => UPLOAD_ERR_OK,
            'size' => 123,
        ];

        $this->fileUploaderMock->method('upload')
                               ->willReturn('http://localhost/f/shorturl');

        // Replace the actual FileUploader with mock for this test
        $uploadController = new class($this->fileUploaderMock) extends UploadController {
            private $mockFileUploader;

            public function __construct($mockFileUploader) {
                $this->mockFileUploader = $mockFileUploader;
            }

            protected function getFileUploader(): FileUploader {
                return $this->mockFileUploader;
            }
        };

        ob_start();
        $uploadController->upload();
        $output = ob_get_clean();

        $this->assertStringContainsString('{"url":"http:\/\/localhost\/f\/shorturl"}', $output);
        $this->assertEquals(200, http_response_code());
    }

    public function testUploadInvalidCsrfToken(): void
    {
        $this->mockServerVariables('/upload', 'POST');
        $_SESSION['csrf_token'] = 'correct_token';
        $_POST['csrf_token'] = 'wrong_token';

        ob_start();
        $this->uploadController->upload();
        $output = ob_get_clean();

        $this->assertStringContainsString('{"error":"Token CSRF invalide"}', $output);
        $this->assertEquals(400, http_response_code());
    }

    public function testUploadNoFileSent(): void
    {
        $this->mockServerVariables('/upload', 'POST');
        $_POST['csrf_token'] = 'test_csrf_token';
        unset($_FILES['file']);

        ob_start();
        $this->uploadController->upload();
        $output = ob_get_clean();

        $this->assertStringContainsString('{"error":"No file sent."}', $output);
        $this->assertEquals(400, http_response_code());
    }

    public function testUploadFileUploaderThrowsException(): void
    {
        $this->mockServerVariables('/upload', 'POST');
        $_POST['csrf_token'] = 'test_csrf_token';
        $_FILES['file'] = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php_upload_test',
            'error' => UPLOAD_ERR_OK,
            'size' => 123,
        ];

        $this->fileUploaderMock->method('upload')
                               ->willThrowException(new RuntimeException('Simulated upload error'));

        $uploadController = new class($this->fileUploaderMock) extends UploadController {
            private $mockFileUploader;

            public function __construct($mockFileUploader) {
                $this->mockFileUploader = $mockFileUploader;
            }

            protected function getFileUploader(): FileUploader {
                return $this->mockFileUploader;
            }
        };

        ob_start();
        $uploadController->upload();
        $output = ob_get_clean();

        $this->assertStringContainsString('{"error":"Simulated upload error"}', $output);
        $this->assertEquals(400, http_response_code());
    }
}