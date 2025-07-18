<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Simple router
switch ($requestUri) {
    case '/':
        $controller = new App\Controllers\HomeController();
        echo $controller->index();
        break;
    case '/upload':
        if ($requestMethod === 'POST') {
            $controller = new App\Controllers\UploadController();
            echo $controller->upload();
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    case '/delete':
        if ($requestMethod === 'POST') {
            $controller = new App\Controllers\DeleteController();
            echo $controller->delete($_POST['hash']);
        } else {
            http_response_code(405);
            echo 'Method Not Allowed';
        }
        break;
    default:
        if (preg_match('#^/f/([a-zA-Z0-9_-]+)$#', $requestUri, $matches)) {
            $controller = new App\Controllers\FileController();
            $controller->download($matches[1]);
        } else {
            http_response_code(404);
            echo 'Not Found';
        }
        break;
}
