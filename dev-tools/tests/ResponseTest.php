<?php declare(strict_types=1);

namespace App\Tests;

use App\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testResponseCreation(): void
    {
        $response = new Response('Hello World', 200, ['Content-Type' => 'text/plain']);
        
        $this->assertEquals('Hello World', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['Content-Type' => 'text/plain'], $response->getHeaders());
    }

    public function testResponseWithDefaultValues(): void
    {
        $response = new Response('Test');
        
        $this->assertEquals('Test', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
    }

    public function testResponseWithDifferentStatusCode(): void
    {
        $response = new Response('Not Found', 404);
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testResponseSend(): void
    {
        $response = new Response('Test content', 201, ['X-Custom' => 'value']);
        
        ob_start();
        $response->send();
        $output = ob_get_clean();
        
        $this->assertEquals('Test content', $output);
    }
}