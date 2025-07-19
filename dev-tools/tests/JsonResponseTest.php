<?php declare(strict_types=1);

namespace App\Tests;

use App\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

class JsonResponseTest extends TestCase
{
    public function testJsonResponseCreation(): void
    {
        $data = ['message' => 'success', 'id' => 123];
        $response = new JsonResponse($data, 201);
        
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['Content-Type' => 'application/json'], $response->getHeaders());
    }

    public function testJsonResponseWithDefaultStatus(): void
    {
        $data = ['error' => 'Something went wrong'];
        $response = new JsonResponse($data);
        
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testJsonResponseWithAdditionalHeaders(): void
    {
        $data = ['test' => 'value'];
        $response = new JsonResponse($data, 200, ['X-Custom' => 'header']);
        
        $expectedHeaders = [
            'Content-Type' => 'application/json',
            'X-Custom' => 'header'
        ];
        $this->assertEquals($expectedHeaders, $response->getHeaders());
    }

    public function testJsonResponseSend(): void
    {
        $data = ['message' => 'test'];
        $response = new JsonResponse($data);
        
        ob_start();
        $response->send();
        $output = ob_get_clean();
        
        $this->assertEquals(json_encode($data), $output);
    }
}