<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Models\LocalStorage;
use App\Models\StorageInterface;

final class LocalStorageTest extends TestCase
{
    private const UPLOAD_DIR = __DIR__ . '/test_uploads';

    protected function setUp(): void
    {
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir(self::UPLOAD_DIR)) {
            array_map('unlink', glob(self::UPLOAD_DIR . '/*'));
            rmdir(self::UPLOAD_DIR);
        }
    }

    public function testConstructorThrowsExceptionIfUploadDirDoesNotExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Le dossier d\'upload n\'existe pas ou n\'est pas accessible en écriture.');
        new LocalStorage(self::UPLOAD_DIR . '_non_existent');
    }

    public function testConstructorThrowsExceptionIfUploadDirIsNotWritable(): void
    {
        // Create a directory that is not writable
        mkdir(self::UPLOAD_DIR . '_unwritable', 0444);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Le dossier d\'upload n\'existe pas ou n\'est pas accessible en écriture.');
        new LocalStorage(self::UPLOAD_DIR . '_unwritable');
        rmdir(self::UPLOAD_DIR . '_unwritable'); // Clean up
    }

    public function testStoreMethod(): void
    {
        $localStorage = new LocalStorage(self::UPLOAD_DIR);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'test content');

        $destinationName = 'my_test_file.txt';
        $storedPath = $localStorage->save($tmpFile, $destinationName);

        $this->assertFileExists($storedPath);
        $this->assertEquals(self::UPLOAD_DIR . '/' . $destinationName, $storedPath);
        $this->assertEquals('test content', file_get_contents($storedPath));

        unlink($tmpFile); // Clean up temp file
    }

    public function testStoreMethodThrowsExceptionOnMoveFailure(): void
    {
        $localStorage = new LocalStorage(self::UPLOAD_DIR);

        $tmpFile = '/non/existent/path/to/file.txt'; // Simulate a non-existent temp file
        $destinationName = 'my_test_file.txt';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Impossible de déplacer le fichier uploadé.');
        $localStorage->save($tmpFile, $destinationName);
    }
}