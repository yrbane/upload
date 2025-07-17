<?php declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Vérification CSRF
if (!isset($_POST['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    http_response_code(400);
    echo json_encode(['error' => 'Token CSRF invalide']);
    exit;
}

require __DIR__ . '/../vendor/autoload.php';

use App\LocalStorage;
use App\UrlShortener;
use App\CookieManager;
use App\FileUploader;

try {
    $baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
              . '://' . $_SERVER['HTTP_HOST']
              . rtrim(dirname($_SERVER['PHP_SELF']), '/');

    $uploader = new FileUploader(
        new LocalStorage(__DIR__ . '/../uploads'),
        new UrlShortener(__DIR__ . '/../data/files.db', $baseHost . '/f'),
        new CookieManager()
    );

    if (!isset($_FILES['file'])) {
        throw new RuntimeException('No file sent.');
    }

    $shortUrl = $uploader->upload($_FILES['file']);
    echo json_encode(['url' => $shortUrl], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
}
