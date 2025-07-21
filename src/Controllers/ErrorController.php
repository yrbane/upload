<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Services\LocalizationService;

class ErrorController
{
    private LocalizationService $localizationService;

    public function __construct()
    {
        $this->localizationService = new LocalizationService();
    }

    public function notFound(): Response
    {
        $translations = $this->getTranslations();
        $currentLocale = $this->localizationService->getCurrentLocale();

        ob_start();
        include __DIR__ . '/../Views/error404.php';
        $content = ob_get_clean();

        return new Response($content, 404, ['Content-Type' => 'text/html; charset=utf-8']);
    }
    
    private function getTranslations(): array
    {
        return [
            'app' => [
                'title' => $this->localizationService->translate('app.title'),
                'picsum_link' => $this->localizationService->translate('app.picsum_link'),
                'papirus_link' => $this->localizationService->translate('app.papirus_link'),
                'github_link' => $this->localizationService->translate('app.github_link')
            ],
            'error' => [
                'page_not_found' => $this->localizationService->translate('error.page_not_found'),
                'page_not_found_description' => $this->localizationService->translate('error.page_not_found_description'),
                'back_to_home' => $this->localizationService->translate('error.back_to_home')
            ]
        ];
    }
}