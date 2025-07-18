<?php declare(strict_types=1);

namespace App\Http;

class BinaryResponse extends Response
{
    public function __construct(
        string $content,
        ?string $mimeType = null,
        ?string $filename = null,
        bool $attachment = true
    ) {
        $headers = [];
        
        $headers['Content-Type'] = $mimeType ?? 'application/octet-stream';
        
        if ($filename) {
            $disposition = $attachment ? 'attachment' : 'inline';
            $headers['Content-Disposition'] = $disposition . '; filename="' . $filename . '"';
        }
        
        parent::__construct($content, 200, $headers);
    }
}