<?php declare(strict_types=1);

namespace App\Services;

class LocalizationService
{
    private const DEFAULT_LOCALE = 'fr';
    private const SUPPORTED_LOCALES = ['fr', 'en', 'es', 'de', 'it', 'pt', 'ar', 'zh'];
    
    private const LANGUAGE_NAMES = [
        'fr' => 'Français',
        'en' => 'English',
        'es' => 'Español',
        'de' => 'Deutsch',
        'it' => 'Italiano',
        'pt' => 'Português',
        'ar' => 'العربية',
        'zh' => '中文'
    ];

    private string $currentLocale;
    private array $translations = [];

    public function __construct()
    {
        $this->currentLocale = $this->detectLocale();
        $this->loadTranslations();
    }

    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    public function setLocale(string $locale): void
    {
        if ($this->isLocaleSupported($locale)) {
            $this->currentLocale = $locale;
            $this->loadTranslations();
        }
    }

    public function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED_LOCALES, true);
    }

    public function getAvailableLanguages(): array
    {
        return self::LANGUAGE_NAMES;
    }

    public function translate(string $key, array $parameters = []): string
    {
        $translation = $this->getTranslation($key);
        
        if (empty($parameters)) {
            return $translation;
        }

        return $this->replaceParameters($translation, $parameters);
    }

    public function detectLocale(): string
    {
        // 1. Check for a language preference in the cookie
        if (isset($_COOKIE['lang'])) {
            $lang = $_COOKIE['lang'];
            if ($this->isLocaleSupported($lang)) {
                return $lang;
            }
        }

        // 2. Check the Accept-Language header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $languages = $this->parseAcceptLanguage($acceptLanguage);
            foreach ($languages as $language) {
                $locale = $this->extractLocaleFromLanguage($language);
                if ($this->isLocaleSupported($locale)) {
                    return $locale;
                }
            }
        }

        // 3. Fallback to the default locale
        return self::DEFAULT_LOCALE;
    }

    private function loadTranslations(): void
    {
        $translationFile = __DIR__ . '/../../translations/' . $this->currentLocale . '.php';
        
        if (file_exists($translationFile)) {
            $this->translations = include $translationFile;
        } else {
            $this->translations = [];
        }
    }

    private function getTranslation(string $key): string
    {
        $keys = explode('.', $key);
        $translation = $this->translations;
        
        foreach ($keys as $keyPart) {
            if (isset($translation[$keyPart])) {
                $translation = $translation[$keyPart];
            } else {
                return $key; // Return key if translation not found
            }
        }
        
        return is_string($translation) ? $translation : $key;
    }

    private function replaceParameters(string $translation, array $parameters): string
    {
        foreach ($parameters as $key => $value) {
            $translation = str_replace('{' . $key . '}', (string) $value, $translation);
        }
        
        return $translation;
    }

    private function parseAcceptLanguage(string $acceptLanguage): array
    {
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, ';') !== false) {
                [$language] = explode(';', $part, 2);
            } else {
                $language = $part;
            }
            $languages[] = trim($language);
        }
        
        return $languages;
    }

    private function extractLocaleFromLanguage(string $language): string
    {
        $parts = explode('-', $language);
        return strtolower($parts[0]);
    }
}