<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Http\BinaryResponse;
use App\Services\FileService;
use App\Models\UrlShortener;

class FileController
{
    private FileService $fileService;

    public function __construct(?FileService $fileService = null)
    {
        $this->fileService = $fileService ?? $this->createFileService();
    }
    
    private function createFileService(): FileService
    {
        $baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                  . rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/');
        
        return new FileService(
            new UrlShortener(__DIR__ . '/../../data/files.db', $baseHost . '/f')
        );
    }

    public function download(string $hash): Response
    {
        $result = $this->fileService->prepareFileForDownload($hash);
        
        if (!$result['success']) {
            return new Response($result['error'], 404);
        }
        
        return new BinaryResponse(
            $result['content'],
            $result['mimeType'],
            $result['filename']
        );
    }
}
