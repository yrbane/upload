<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\CookieManager;

class HomeController
{
    protected function getCookieManager(): CookieManager
    {
        return new CookieManager();
    }

    protected function getUrlShortener(string $baseHost): \App\Models\UrlShortener
    {
        return new \App\Models\UrlShortener(__DIR__ . '/../../data/files.db', $baseHost . '/f');
    }

    public function index()
    {

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . $_SERVER['HTTP_HOST']
                  . rtrim(dirname($_SERVER['PHP_SELF']), '/');

        $cookieManager  = $this->getCookieManager();
        $uploadedHashes = $cookieManager->getUploadedHashes();

        $shortener = $this->getUrlShortener($baseHost);
        $uploadedFiles = [];
        foreach ($uploadedHashes as $hash) {
            $fileData = $shortener->resolve($hash);
            if ($fileData) {
                $uploadedFiles[$hash] = [
                    'filename' => $fileData['filename'],
                    'mime_type' => $fileData['mime_type']
                ];
            }
        }

        require __DIR__ . '/../Views/home.php';
    }
}
