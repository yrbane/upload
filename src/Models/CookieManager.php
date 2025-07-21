<?php declare(strict_types=1);

namespace App\Models;

/**
 * Manages a cookie storing uploaded file hashes.
 */
class CookieManager
{
    private const COOKIE_NAME     = 'uploaded_files';
    private const COOKIE_LIFETIME = 2592000; // 30 days in seconds

    /**
     * @return string[] Array of saved hashes from the cookie.
     */
    public function getUploadedHashes(): array
    {
        if (!isset($_COOKIE[self::COOKIE_NAME])) {
            return [];
        }
        $data = json_decode($_COOKIE[self::COOKIE_NAME], true, 512, JSON_THROW_ON_ERROR);
        return is_array($data) ? $data : [];
    }

    /**
     * Append a hash and reset the cookie.
     */
    public function addHash(string $hash): void
    {
        $hashes = $this->getUploadedHashes();
        $hashes[] = $hash;
        setcookie(
            self::COOKIE_NAME,
            json_encode($hashes, JSON_THROW_ON_ERROR),
            [
                'expires'  => time() + self::COOKIE_LIFETIME,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    /**
     * Remove a hash and reset the cookie.
     */
    public function removeHash(string $hashToRemove): void
    {
        $hashes = $this->getUploadedHashes();
        $hashes = array_filter($hashes, fn($hash) => $hash !== $hashToRemove);
        setcookie(
            self::COOKIE_NAME,
            json_encode(array_values($hashes), JSON_THROW_ON_ERROR), // Re-index array
            [
                'expires'  => time() + self::COOKIE_LIFETIME,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }
}
