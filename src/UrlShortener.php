<?php declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use RuntimeException;

/**
 * URL shortener using SQLite for mapping.
 */
class UrlShortener
{
    private PDO $pdo;
    private string $baseUrl;

    /**
     * @param string $databasePath Path to SQLite file (will be created).
     * @param string $baseUrl      Base URL for shortened links (e.g. https://example.com/f/).
     *
     * @throws RuntimeException on DB connection failure.
     */
    public function __construct(string $databasePath, string $baseUrl)
    {
        try {
            $this->pdo = new PDO("sqlite:{$databasePath}");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new RuntimeException('SQLite connection failed: ' . $e->getMessage());
        }

        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->initSchema();
    }

    private function initSchema(): void
    {
        $this->pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS files (
    hash TEXT PRIMARY KEY,
    path TEXT       NOT NULL,
    created_at TEXT NOT NULL
)
SQL
        );
    }

    /**
     * Store a mapping hash → file path and return the full short URL.
     */
    public function shorten(string $filePath): string
    {
        $hash = bin2hex(random_bytes(6)); // 12 hex chars
        $stmt = $this->pdo->prepare(
            'INSERT INTO files (hash, path, created_at) VALUES (:hash, :path, :created_at)'
        );
        $stmt->execute([
            ':hash'       => $hash,
            ':path'       => $filePath,
            ':created_at' => (new \DateTimeImmutable())->format('c'),
        ]);

        return $this->baseUrl . $hash;
    }

    /**
     * Resolve a hash back to the stored file path, or null if not found.
     */
    public function resolve(string $hash): ?string
    {
        $stmt = $this->pdo->prepare('SELECT path FROM files WHERE hash = :hash');
        $stmt->execute([':hash' => $hash]);
        $path = $stmt->fetchColumn();
        return $path !== false ? $path : null;
    }
}
