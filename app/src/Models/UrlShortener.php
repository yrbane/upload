<?php declare(strict_types=1);

namespace App\Models;

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
    filename TEXT   NOT NULL,
    created_at TEXT NOT NULL
)
SQL
        );

        // Add filename column if it doesn't exist
        $stmt = $this->pdo->query("PRAGMA table_info(files);");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('filename', $columns)) {
            $this->pdo->exec("ALTER TABLE files ADD COLUMN filename TEXT;");
        }

        // Add mime_type column if it doesn't exist
        $stmt = $this->pdo->query("PRAGMA table_info(files);");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('mime_type', $columns)) {
            $this->pdo->exec("ALTER TABLE files ADD COLUMN mime_type TEXT;");
        }
    }

    /**
     * Store a mapping hash → file path and return the full short URL.
     */
    public function shorten(string $filePath, string $filename, string $mimeType): string
    {
        $hash = bin2hex(random_bytes(6)); // 12 hex chars
        $stmt = $this->pdo->prepare(
            'INSERT INTO files (hash, path, filename, mime_type, created_at) VALUES (:hash, :path, :filename, :mime_type, :created_at)'
        );
        $stmt->execute([
            ':hash'       => $hash,
            ':path'       => $filePath,
            ':filename'   => $filename,
            ':mime_type'  => $mimeType,
            ':created_at' => (new \DateTimeImmutable())->format('c'),
        ]);

        return $this->baseUrl . $hash;
    }

    /**
     * Resolve a hash back to the stored file path and filename, or null if not found.
     */
    public function resolve(string $hash): ?array
    {
        $stmt = $this->pdo->prepare('SELECT path, filename, mime_type FROM files WHERE hash = :hash');
        $stmt->execute([':hash' => $hash]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Delete a file record by hash.
     */
    public function deleteFile(string $hash): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM files WHERE hash = :hash');
        return $stmt->execute([':hash' => $hash]);
    }
}
