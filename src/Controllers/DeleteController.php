<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Services\DeleteService;
use App\Models\UrlShortener;
use App\Models\CookieManager;

class DeleteController
{
    private DeleteService $deleteService;

    public function __construct(?DeleteService $deleteService = null)
    {
        $this->deleteService = $deleteService ?? $this->createDeleteService();
    }
    
    private function createDeleteService(): DeleteService
    {
        $baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                  . rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/');
        
        return new DeleteService(
            new UrlShortener(__DIR__ . '/../../data/files.db', $baseHost . '/f'),
            new CookieManager()
        );
    }

    public function delete(string $hash): JsonResponse
    {
        $result = $this->deleteService->deleteFile($hash);
        
        if ($result['success']) {
            return new JsonResponse(['success' => true], 200);
        }
        
        $statusCode = match ($result['error']) {
            'Invalid CSRF token.' => 400,
            'You are not authorized to delete this file.' => 403,
            'File not found in database.' => 404,
            default => 400
        };
        
        return new JsonResponse(['error' => $result['error']], $statusCode);
    }
}