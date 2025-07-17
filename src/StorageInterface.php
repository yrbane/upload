<?php declare(strict_types=1);

namespace App;

/**
 * Interface for file storage backends.
 */
interface StorageInterface
{
    /**
     * Save an uploaded file.
     *
     * @param string $tmpPath Temporary file path.
     * @param string $destinationName Desired file name.
     *
     * @return string Stored file path.
     */
    public function save(string $tmpPath, string $destinationName): string;
}
