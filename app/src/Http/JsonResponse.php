<?php declare(strict_types=1);

namespace App\Http;

class JsonResponse extends Response
{
    public function __construct(array $data, int $statusCode = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        parent::__construct(json_encode($data), $statusCode, $headers);
    }
}