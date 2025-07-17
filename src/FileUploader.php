<?php declare(strict_types=1);

namespace App;

use RuntimeException;

/**
 * Coordinates upload → storage → URL shortening → cookie.
 */
class FileUploader
{
    public function __construct(
        private StorageInterface $storage,
        private UrlShortener    $shortener,
        private CookieManager   $cookieManager
    ) {}

    /**
     * @param array $file  One entry from $_FILES.
     * @return string Short URL.
     *
     * @throws RuntimeException on validation or storage errors.
     */
    public function upload(array $file): string
    {
        // 1. Basic error & size check
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload error code: ' . $file['error']);
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new RuntimeException('File too large (max 10 MiB).');
        }

        // 2. Mime-type whitelist
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException('Invalid file type.');
        }

        // 3. Génération d’un nom unique
        $ext             = pathinfo((string) $file['name'], PATHINFO_EXTENSION);
        $destinationName = bin2hex(random_bytes(8)) . '.' . $ext;

        // 4. Stockage
        $storedPath = $this->storage->save($file['tmp_name'], $destinationName);

        // 5. Raccourcissement
        $shortUrl = $this->shortener->shorten($storedPath);

        // 6. Cookie tracking
        $hash = basename($shortUrl);
        $this->cookieManager->addHash($hash);

        return $shortUrl;
    }
}
