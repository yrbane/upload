<?php declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use App\UrlShortener;

$hash = $_GET['hash'] ?? '';
$shortener = new UrlShortener(__DIR__ . '/../data/files.db', '');
$file = $shortener->resolve($hash);

if (!$file || !file_exists($file)) {
    http_response_code(404);
    exit('File not found');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
readfile($file);
