<?php declare(strict_types=1);

namespace App\Models;



use RuntimeException;

/**
 * Local storage implementation of StorageInterface.
 */
class LocalStorage implements StorageInterface
{
    protected string $uploadDir;

    /**
     * @param string $uploadDir Absolute path to upload directory.
     *
     * @throws RuntimeException if the directory can't be created.
     */
    public function __construct(string $uploadDir)
    {
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            throw new RuntimeException('Le dossier d\'upload n\'existe pas ou n\'est pas accessible en écriture.');
        }
        $this->uploadDir = rtrim($uploadDir, '/');
    }

    public function save(string $tmpPath, string $destinationName): string
    {
        $destination = "{$this->uploadDir}/{$destinationName}";
        
        // Use move_uploaded_file for uploaded files, copy for test files
        if (is_uploaded_file($tmpPath)) {
            if (!move_uploaded_file($tmpPath, $destination)) {
                throw new RuntimeException('Impossible de déplacer le fichier uploadé.');
            }
        } else {
            // For testing purposes, use copy instead of move_uploaded_file
            if (!copy($tmpPath, $destination)) {
                throw new RuntimeException('Impossible de déplacer le fichier uploadé.');
            }
        }
        
        return $destination;
    }
}
