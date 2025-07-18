<?php declare(strict_types=1);

namespace App\Tests;

use App\Controllers\HomeController;
use App\Controllers\UploadController;
use App\Controllers\FileController;
use App\Controllers\DeleteController;
use App\Services\HomeService;
use App\Services\UploadService;
use App\Services\FileService;
use App\Services\DeleteService;
use App\Http\Response;
use App\Http\JsonResponse;
use App\Http\BinaryResponse;
use PHPUnit\Framework\TestCase;

class ControllersIntegrationTest extends TestCase
{
    public function testHomeControllerReturnsResponse(): void
    {
        $homeService = $this->createMock(HomeService::class);
        $homeService->method('getHomePageData')
                    ->willReturn([
                        'csrfToken' => 'test-token',
                        'baseHost' => 'http://localhost',
                        'uploadedFiles' => []
                    ]);

        $controller = new HomeController($homeService);
        $response = $controller->index();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['Content-Type' => 'text/html'], $response->getHeaders());
    }

    public function testUploadControllerReturnsJsonResponse(): void
    {
        $uploadService = $this->createMock(UploadService::class);
        $uploadService->method('processUpload')
                      ->willReturn([
                          'success' => true,
                          'url' => 'https://example.com/f/abc123',
                          'error' => ''
                      ]);

        $controller = new UploadController($uploadService);
        $response = $controller->upload();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['Content-Type' => 'application/json'], $response->getHeaders());
        $this->assertStringContainsString('abc123', $response->getContent());
    }

    public function testUploadControllerReturnsErrorResponse(): void
    {
        $uploadService = $this->createMock(UploadService::class);
        $uploadService->method('processUpload')
                      ->willReturn([
                          'success' => false,
                          'url' => null,
                          'error' => 'Token CSRF invalide'
                      ]);

        $controller = new UploadController($uploadService);
        $response = $controller->upload();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Token CSRF invalide', $response->getContent());
    }

    public function testFileControllerReturnsBinaryResponse(): void
    {
        $fileService = $this->createMock(FileService::class);
        $fileService->method('prepareFileForDownload')
                    ->willReturn([
                        'success' => true,
                        'content' => 'file content',
                        'mimeType' => 'text/plain',
                        'filename' => 'test.txt',
                        'error' => ''
                    ]);

        $controller = new FileController($fileService);
        $response = $controller->download('abc123');

        $this->assertInstanceOf(BinaryResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('file content', $response->getContent());
    }

    public function testFileControllerReturnsErrorResponse(): void
    {
        $fileService = $this->createMock(FileService::class);
        $fileService->method('prepareFileForDownload')
                    ->willReturn([
                        'success' => false,
                        'content' => null,
                        'mimeType' => null,
                        'filename' => null,
                        'error' => 'File not found'
                    ]);

        $controller = new FileController($fileService);
        $response = $controller->download('nonexistent');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('File not found', $response->getContent());
    }

    public function testDeleteControllerReturnsSuccessResponse(): void
    {
        $deleteService = $this->createMock(DeleteService::class);
        $deleteService->method('deleteFile')
                      ->willReturn([
                          'success' => true,
                          'error' => ''
                      ]);

        $controller = new DeleteController($deleteService);
        $response = $controller->delete('abc123');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('true', $response->getContent());
    }

    public function testDeleteControllerReturnsErrorResponse(): void
    {
        $deleteService = $this->createMock(DeleteService::class);
        $deleteService->method('deleteFile')
                      ->willReturn([
                          'success' => false,
                          'error' => 'Invalid CSRF token.'
                      ]);

        $controller = new DeleteController($deleteService);
        $response = $controller->delete('abc123');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid CSRF token.', $response->getContent());
    }
}