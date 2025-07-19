<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\CookieManager;

final class CookieManagerTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear any existing cookies for a clean test environment
        if (isset($_COOKIE['uploaded_files'])) {
            unset($_COOKIE['uploaded_files']);
            setcookie('uploaded_files', '', time() - 3600, '/');
        }
    }

    public function testGetUploadedHashesReturnsEmptyArrayInitially(): void
    {
        $cookieManager = new CookieManager();
        $this->assertEmpty($cookieManager->getUploadedHashes());
    }

    public function testAddHash(): void
    {
        $cookieManager = new CookieManager();
        $hash1 = 'abc123def456';
        $cookieManager->addHash($hash1);

        // Simulate cookie being set for the next request
        $_COOKIE['uploaded_files'] = json_encode([$hash1]);

        $this->assertEquals([$hash1], $cookieManager->getUploadedHashes());

        $hash2 = 'ghi789jkl012';
        $cookieManager->addHash($hash2);

        // Simulate cookie being set for the next request
        $_COOKIE['uploaded_files'] = json_encode([$hash1, $hash2]);

        $this->assertEquals([$hash1, $hash2], $cookieManager->getUploadedHashes());
    }

    public function testRemoveHash(): void
    {
        $cookieManager = new CookieManager();
        $hash1 = 'abc123def456';
        $hash2 = 'ghi789jkl012';
        $hash3 = 'mno345pqr678';

        // Set initial cookie state
        $_COOKIE['uploaded_files'] = json_encode([$hash1, $hash2, $hash3]);

        $cookieManager->removeHash($hash2);

        // Simulate cookie being set for the next request
        $_COOKIE['uploaded_files'] = json_encode([$hash1, $hash3]);

        $this->assertEquals([$hash1, $hash3], $cookieManager->getUploadedHashes());

        $cookieManager->removeHash($hash1);
        $_COOKIE['uploaded_files'] = json_encode([$hash3]);
        $this->assertEquals([$hash3], $cookieManager->getUploadedHashes());

        $cookieManager->removeHash($hash3);
        $_COOKIE['uploaded_files'] = json_encode([]);
        $this->assertEmpty($cookieManager->getUploadedHashes());
    }
}