<?php declare(strict_types=1);

namespace App\Services;

use App\Models\UrlShortener;

class FileService
{
    private UrlShortener $urlShortener;

    public function __construct(UrlShortener $urlShortener)
    {
        $this->urlShortener = $urlShortener;
    }

    public function getFileData(string $hash): ?array
    {
        return $this->urlShortener->resolve($hash);
    }

    public function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    public function getFileContent(string $path): string
    {
        return file_get_contents($path);
    }

    public function prepareFileForDownload(string $hash): array
    {
        $fileData = $this->getFileData($hash);
        
        if (!$fileData) {
            return [
                'success' => false,
                'error' => 'File not found',
                'content' => null,
                'mimeType' => null,
                'filename' => null
            ];
        }

        if (!$this->fileExists($fileData['path'])) {
            return [
                'success' => false,
                'error' => 'File not found',
                'content' => null,
                'mimeType' => null,
                'filename' => null
            ];
        }

        $content = $this->getFileContent($fileData['path']);
        
        return [
            'success' => true,
            'content' => $content,
            'mimeType' => $fileData['mime_type'] ?? 'application/octet-stream',
            'filename' => $fileData['filename'],
            'error' => ''
        ];
    }
}