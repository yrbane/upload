<?php declare(strict_types=1);

namespace App\Services;

use App\Models\CookieManager;
use App\Models\UrlShortener;

class HomeService
{
    private CookieManager $cookieManager;
    private UrlShortener $urlShortener;

    public function __construct(CookieManager $cookieManager, UrlShortener $urlShortener)
    {
        $this->cookieManager = $cookieManager;
        $this->urlShortener = $urlShortener;
    }

    public function generateCsrfToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function getBaseHost(): string
    {
        return (isset($_SERVER['HTTPS']) ? 'https' : 'http')
             . '://' . $_SERVER['HTTP_HOST']
             . rtrim(dirname($_SERVER['PHP_SELF']), '/');
    }

    public function getUploadedFilesData(): array
    {
        $uploadedHashes = $this->cookieManager->getUploadedHashes();
        $uploadedFiles = [];
        
        foreach ($uploadedHashes as $hash) {
            $fileData = $this->urlShortener->resolve($hash);
            if ($fileData) {
                $uploadedFiles[$hash] = [
                    'filename' => $fileData['filename'],
                    'mime_type' => $fileData['mime_type']
                ];
            }
        }
        
        return $uploadedFiles;
    }

    public function getHomePageData(): array
    {
        return [
            'csrfToken' => $this->generateCsrfToken(),
            'baseHost' => $this->getBaseHost(),
            'uploadedFiles' => $this->getUploadedFilesData()
        ];
    }
}