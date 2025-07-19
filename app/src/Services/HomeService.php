<?php declare(strict_types=1);

namespace App\Services;

use App\Models\CookieManager;
use App\Models\UrlShortener;

class HomeService
{
    private CookieManager $cookieManager;
    private UrlShortener $urlShortener;
    private LocalizationService $localizationService;

    public function __construct(CookieManager $cookieManager, UrlShortener $urlShortener, LocalizationService $localizationService)
    {
        $this->cookieManager = $cookieManager;
        $this->urlShortener = $urlShortener;
        $this->localizationService = $localizationService;
    }

    public function generateCsrfToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function getBaseHost(): string
    {
        return (isset($_SERVER['HTTPS']) ? 'https' : 'http')
             . '://' . $_SERVER['HTTP_HOST']
             . rtrim(dirname($_SERVER['PHP_SELF']), '/');
    }

    public function getUploadedFilesData(): array
    {
        $uploadedHashes = $this->cookieManager->getUploadedHashes();
        $uploadedFiles = [];
        
        foreach ($uploadedHashes as $hash) {
            $fileData = $this->urlShortener->resolve($hash);
            if ($fileData) {
                $uploadedFiles[$hash] = [
                    'filename' => $fileData['filename'],
                    'mime_type' => $fileData['mime_type']
                ];
            }
        }
        
        return $uploadedFiles;
    }

    public function getHomePageData(): array
    {
        return [
            'csrfToken' => $this->generateCsrfToken(),
            'baseHost' => $this->getBaseHost(),
            'uploadedFiles' => $this->getUploadedFilesData(),
            'translations' => $this->getTranslations(),
            'currentLocale' => $this->localizationService->getCurrentLocale()
        ];
    }
    
    private function getTranslations(): array
    {
        return [
            'app' => [
                'title' => $this->localizationService->translate('app.title'),
                'upload' => $this->localizationService->translate('app.upload'),
                'select_file' => $this->localizationService->translate('app.select_file'),
                'drag_drop' => $this->localizationService->translate('app.drag_drop'),
                'my_files' => $this->localizationService->translate('app.my_files'),
                'no_files' => $this->localizationService->translate('app.no_files'),
                'delete' => $this->localizationService->translate('app.delete'),
                'confirm_delete' => $this->localizationService->translate('app.confirm_delete'),
                'copy_link' => $this->localizationService->translate('app.copy_link'),
                'language' => $this->localizationService->translate('app.language'),
                'change_language' => $this->localizationService->translate('app.change_language'),
                'picsum_link' => $this->localizationService->translate('app.picsum_link'),
                'papirus_link' => $this->localizationService->translate('app.papirus_link'),
                'github_link' => $this->localizationService->translate('app.github_link'),
                'uploaded' => $this->localizationService->translate('app.uploaded'),
                'upload_failed' => $this->localizationService->translate('app.upload_failed'),
                'error_deleting' => $this->localizationService->translate('app.error_deleting'),
                'separator' => $this->localizationService->translate('app.separator')
            ],
            'success' => [
                'upload_complete' => $this->localizationService->translate('success.upload_complete')
            ]
        ];
    }
}