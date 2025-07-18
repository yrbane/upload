<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UrlShortener;
use App\Models\CookieManager;

class DeleteController
{
    protected function getUrlShortener(): UrlShortener
    {
        $baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . $_SERVER['HTTP_HOST']
                  . rtrim(dirname($_SERVER['PHP_SELF']), '/');
        return new UrlShortener(__DIR__ . '/../../data/files.db', $baseHost . '/f');
    }

    protected function getCookieManager(): CookieManager
    {
        return new CookieManager();
    }

    public function delete(string $hash)
    {
        header('Content-Type: application/json');

        // CSRF Token verification
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(400);
            return json_encode(['error' => 'Invalid CSRF token.']);
        }

        $urlShortener = $this->getUrlShortener();
        $cookieManager = $this->getCookieManager();

        // Check if the hash belongs to the user
        $uploadedHashes = $cookieManager->getUploadedHashes();
        if (!in_array($hash, $uploadedHashes)) {
            http_response_code(403);
            return json_encode(['error' => 'You are not authorized to delete this file.']);
        }

        $fileData = $urlShortener->resolve($hash);

        if (!$fileData) {
            http_response_code(404);
            return json_encode(['error' => 'File not found in database.']);
        }

        // Delete physical file
        if (file_exists($fileData['path'])) {
            unlink($fileData['path']);
        }

        // Delete from database
        $urlShortener->deleteFile($hash);

        // Remove from cookie
        $cookieManager->removeHash($hash);

        return json_encode(['success' => true]);
    }
}