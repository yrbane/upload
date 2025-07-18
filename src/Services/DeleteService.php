<?php declare(strict_types=1);

namespace App\Services;

use App\Models\UrlShortener;
use App\Models\CookieManager;

class DeleteService
{
    private UrlShortener $urlShortener;
    private CookieManager $cookieManager;
    private LocalizationService $localizationService;

    public function __construct(UrlShortener $urlShortener, CookieManager $cookieManager, LocalizationService $localizationService)
    {
        $this->urlShortener = $urlShortener;
        $this->cookieManager = $cookieManager;
        $this->localizationService = $localizationService;
    }

    public function validateCsrfToken(): array
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return ['valid' => false, 'error' => $this->localizationService->translate('error.csrf_invalid')];
        }

        return ['valid' => true, 'error' => ''];
    }

    public function validateUserAuthorization(string $hash): array
    {
        $uploadedHashes = $this->cookieManager->getUploadedHashes();
        
        if (!in_array($hash, $uploadedHashes)) {
            return ['valid' => false, 'error' => $this->localizationService->translate('error.unauthorized')];
        }

        return ['valid' => true, 'error' => ''];
    }

    public function deleteFile(string $hash): array
    {
        $csrfValidation = $this->validateCsrfToken();
        if (!$csrfValidation['valid']) {
            return [
                'success' => false,
                'error' => $csrfValidation['error']
            ];
        }

        $authValidation = $this->validateUserAuthorization($hash);
        if (!$authValidation['valid']) {
            return [
                'success' => false,
                'error' => $authValidation['error']
            ];
        }

        $fileData = $this->urlShortener->resolve($hash);
        if (!$fileData) {
            return [
                'success' => false,
                'error' => $this->localizationService->translate('error.database_error')
            ];
        }

        // Delete physical file
        if (file_exists($fileData['path'])) {
            unlink($fileData['path']);
        }

        // Delete from database
        $this->urlShortener->deleteFile($hash);

        // Remove from cookie
        $this->cookieManager->removeHash($hash);

        return [
            'success' => true,
            'error' => ''
        ];
    }
}