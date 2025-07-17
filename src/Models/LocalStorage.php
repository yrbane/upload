<?php declare(strict_types=1);

namespace App\Models;



use RuntimeException;

/**
 * Local storage implementation of StorageInterface.
 */
class LocalStorage implements StorageInterface
{
    private string $uploadDir;

    /**
     * @param string $uploadDir Absolute path to upload directory.
     *
     * @throws RuntimeException if the directory can't be created.
     */
    public function __construct(string $uploadDir)
    {
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new RuntimeException("Cannot create upload directory: {$uploadDir}");
        }
        $this->uploadDir = rtrim($uploadDir, '/');
    }

    public function save(string $tmpPath, string $destinationName): string
    {
        $destination = "{$this->uploadDir}/{$destinationName}";
        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }
        return $destination;
    }
}
