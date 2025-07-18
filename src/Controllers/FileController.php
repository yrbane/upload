<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UrlShortener;

class FileController
{
    protected function getUrlShortener(): UrlShortener
    {
        $baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . $_SERVER['HTTP_HOST']
                  . rtrim(dirname($_SERVER['PHP_SELF']), '/');
        return new UrlShortener(__DIR__ . '/../../data/files.db', $baseHost . '/f');
    }

    public function download(string $hash)
    {
        $shortener = $this->getUrlShortener();
        $fileData = $shortener->resolve($hash);

        if (!$fileData || !file_exists($fileData['path'])) {
            http_response_code(404);
            exit('File not found');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileData['filename'] . '"');
        readfile($fileData['path']);
    }
}
