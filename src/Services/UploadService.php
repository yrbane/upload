<?php declare(strict_types=1);

namespace App\Services;

use App\Models\FileUploader;

class UploadService
{
    private FileUploader $fileUploader;
    private LocalizationService $localizationService;

    public function __construct(FileUploader $fileUploader, LocalizationService $localizationService)
    {
        $this->fileUploader = $fileUploader;
        $this->localizationService = $localizationService;
    }

    public function validateCsrfToken(): array
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return ['valid' => false, 'error' => $this->localizationService->translate('error.csrf_invalid')];
        }

        return ['valid' => true, 'error' => ''];
    }

    public function validateFileUpload(): array
    {
        if (!isset($_FILES['file'])) {
            return ['valid' => false, 'error' => $this->localizationService->translate('error.file_not_sent')];
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