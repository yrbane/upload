<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\UrlShortener;

final class UrlShortenerTest extends TestCase
{
    private const DB_PATH = __DIR__ . '/test_files.db';
    private const BASE_URL = 'http://localhost/f';

    protected function setUp(): void
    {
        // Ensure a clean database for each test
        if (file_exists(self::DB_PATH)) {
            unlink(self::DB_PATH);
        }
    }

    protected function tearDown(): void
    {
        // Clean up the database after each test
        if (file_exists(self::DB_PATH)) {
            unlink(self::DB_PATH);
        }
    }

    public function testShortenAndResolve(): void
    {
        $shortener = new UrlShortener(self::DB_PATH, self::BASE_URL);

        $filePath = '/path/to/my/file.txt';
        $filename = 'my_file.txt';
        $mimeType = 'text/plain';

        $shortUrl = $shortener->shorten($filePath, $filename, $mimeType);

        $this->assertStringStartsWith(self::BASE_URL . '/', $shortUrl);
        $this->assertMatchesRegularExpression('/^http:\/\/localhost\/f\/[a-f0-9]{12}$/', $shortUrl);

        $hash = basename($shortUrl);
        $resolved = $shortener->resolve($hash);

        $this->assertIsArray($resolved);
        $this->assertEquals($filePath, $resolved['path']);
        $this->assertEquals($filename, $resolved['filename']);
        $this->assertEquals($mimeType, $resolved['mime_type']);
    }

    public function testResolveNonExistentHash(): void
    {
        $shortener = new UrlShortener(self::DB_PATH, self::BASE_URL);
        $resolved = $shortener->resolve('nonexistent');
        $this->assertNull($resolved);
    }

    public function testDeleteFile(): void
    {
        $shortener = new UrlShortener(self::DB_PATH, self::BASE_URL);

        $filePath = '/path/to/another/file.jpg';
        $filename = 'another_file.jpg';
        $mimeType = 'image/jpeg';

        $shortUrl = $shortener->shorten($filePath, $filename, $mimeType);
        $hash = basename($shortUrl);

        $this->assertNotNull($shortener->resolve($hash));

        $shortener->deleteFile($hash);

        $this->assertNull($shortener->resolve($hash));
    }

    public function testInitSchemaAddsColumns(): void
    {
        // Create a dummy DB file without filename and mime_type columns
        $pdo = new PDO('sqlite:' . self::DB_PATH);
        $pdo->exec("CREATE TABLE files (hash TEXT PRIMARY KEY, path TEXT NOT NULL, created_at TEXT NOT NULL);");
        $pdo = null; // Close connection

        // Initialize UrlShortener, which should add the missing columns
        $shortener = new UrlShortener(self::DB_PATH, self::BASE_URL);

        // Verify columns exist
        $pdo = new PDO('sqlite:' . self::DB_PATH);
        $stmt = $pdo->query("PRAGMA table_info(files);");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);

        $this->assertContains('filename', $columns);
        $this->assertContains('mime_type', $columns);
    }
}