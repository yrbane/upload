<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Services\HomeService;
use App\Services\LocalizationService;
use App\Models\CookieManager;
use App\Models\UrlShortener;

class HomeController
{
    private HomeService $homeService;

    public function __construct(?HomeService $homeService = null)
    {
        $this->homeService = $homeService ?? $this->createHomeService();
    }
    
    private function createHomeService(): HomeService
    {
        $baseHost = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                  . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                  . rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/');
        
        $localizationService = new LocalizationService();
        
        // Detect and set language from cookie first, then Accept-Language header
        $detectedLocale = $localizationService->detectLocaleFromCookie();
        if (!$detectedLocale) {
            $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'fr';
            $detectedLocale = $localizationService->detectLocaleFromAcceptLanguage($acceptLanguage);
        }
        $localizationService->setLocale($detectedLocale);
        
        return new HomeService(
            new CookieManager(),
            new UrlShortener(__DIR__ . '/../../data/files.db', $baseHost . '/f'),
            $localizationService
        );
    }

    public function index(): Response
    {
        $data = $this->homeService->getHomePageData();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $data['csrfToken'];
        }
        
        // Extract variables for the view
        $baseHost = $data['baseHost'];
        $uploadedFiles = $data['uploadedFiles'];
        $translations = $data['translations'];
        $currentLocale = $data['currentLocale'];
        
        ob_start();
        require __DIR__ . '/../Views/home.php';
        $content = ob_get_clean();
        
        return new Response($content, 200, ['Content-Type' => 'text/html']);
    }
}
