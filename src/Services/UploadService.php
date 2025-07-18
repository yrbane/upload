<?php declare(strict_types=1);

namespace App\Services;

use App\Models\FileUploader;

class UploadService
{
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function validateCsrfToken(): array
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return ['valid' => false, 'error' => 'Token CSRF invalide'];
        }

        return ['valid' => true, 'error' => ''];
    }

    public function validateFileUpload(): array
    {
        if (!isset($_FILES['file'])) {
            return ['valid' => false, 'error' => 'No file sent.'];
        }

        return ['valid' => true, 'error' => ''];
    }

    public function processUpload(): array
    {
        try {
            $csrfValidation = $this->validateCsrfToken();
            if (!$csrfValidation['valid']) {
                return [
                    'success' => false,
                    'error' => $csrfValidation['error'],
                    'url' => null
                ];
            }

            $fileValidation = $this->validateFileUpload();
            if (!$fileValidation['valid']) {
                return [
                    'success' => false,
                    'error' => $fileValidation['error'],
                    'url' => null
                ];
            }

            $shortUrl = $this->fileUploader->upload($_FILES['file']);
            
            return [
                'success' => true,
                'url' => $shortUrl,
                'error' => ''
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'url' => null
            ];
        }
    }
}