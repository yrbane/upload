<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Services\UploadService;
use App\Models\FileUploader;
use App\Models\LocalStorage;
use App\Models\UrlShortener;
use App\Models\CookieManager;

class UploadController
{
    private UploadService $uploadService;

    public function __construct(?UploadService $uploadService = null)
    {
        $this->uploadService = $uploadService ?? $this->createUploadService();
    }
    
    private function createUploadService(): UploadService
    {
        $baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                  . rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/');

        $fileUploader = new FileUploader(
            new LocalStorage(__DIR__ . '/../../uploads'),
            new UrlShortener(__DIR__ . '/../../data/files.db', $baseHost . '/f'),
            new CookieManager()
        );
        
        return new UploadService($fileUploader);
    }

    public function upload(): JsonResponse
    {
        $result = $this->uploadService->processUpload();
        
        if ($result['success']) {
            return new JsonResponse(['url' => $result['url']], 200);
        }
        
        return new JsonResponse(['error' => $result['error']], 400);
    }
}
