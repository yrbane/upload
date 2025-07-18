<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Response;

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Simple router
switch ($requestUri) {
    case '/':
        $controller = new App\Controllers\HomeController();
        $response = $controller->index();
        $response->send();
        break;
    case '/upload':
        if ($requestMethod === 'POST') {
            $controller = new App\Controllers\UploadController();
            $response = $controller->upload();
            $response->send();
        } else {
            $response = new Response('Method Not Allowed', 405);
            $response->send();
        }
        break;
    case '/delete':
        if ($requestMethod === 'POST') {
            $controller = new App\Controllers\DeleteController();
            $response = $controller->delete($_POST['hash']);
            $response->send();
        } else {
            $response = new Response('Method Not Allowed', 405);
            $response->send();
        }
        break;
    default:
        if (preg_match('#^/f/([a-zA-Z0-9_-]+)$#', $requestUri, $matches)) {
            $controller = new App\Controllers\FileController();
            $response = $controller->download($matches[1]);
            $response->send();
        } else {
            $response = new Response('Not Found', 404);
            $response->send();
        }
        break;
}
