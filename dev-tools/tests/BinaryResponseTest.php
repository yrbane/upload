<?php declare(strict_types=1);

namespace App\Tests;

use App\Http\BinaryResponse;
use PHPUnit\Framework\TestCase;

class BinaryResponseTest extends TestCase
{
    public function testBinaryResponseCreation(): void
    {
        $content = 'binary data';
        $response = new BinaryResponse($content, 'application/octet-stream', 'file.bin');
        
        $this->assertEquals($content, $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        
        $expectedHeaders = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="file.bin"'
        ];
        $this->assertEquals($expectedHeaders, $response->getHeaders());
    }

    public function testBinaryResponseWithDefaultMimeType(): void
    {
        $content = 'test content';
        $response = new BinaryResponse($content, null, 'test.txt');
        
        $expectedHeaders = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="test.txt"'
        ];
        $this->assertEquals($expectedHeaders, $response->getHeaders());
    }

    public function testBinaryResponseInline(): void
    {
        $content = 'image data';
        $response = new BinaryResponse($content, 'image/jpeg', 'photo.jpg', false);
        
        $expectedHeaders = [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="photo.jpg"'
        ];
        $this->assertEquals($expectedHeaders, $response->getHeaders());
    }

    public function testBinaryResponseSend(): void
    {
        $content = 'file content';
        $response = new BinaryResponse($content, 'text/plain', 'test.txt');
        
        ob_start();
        $response->send();
        $output = ob_get_clean();
        
        $this->assertEquals($content, $output);
    }
}