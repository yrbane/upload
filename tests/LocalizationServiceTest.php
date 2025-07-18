<?php declare(strict_types=1);

namespace App\Tests;

use App\Services\LocalizationService;
use PHPUnit\Framework\TestCase;

class LocalizationServiceTest extends TestCase
{
    private LocalizationService $localizationService;

    protected function setUp(): void
    {
        $this->localizationService = new LocalizationService();
    }

    public function testDefaultLocaleIsFrench(): void
    {
        $this->assertEquals('fr', $this->localizationService->getCurrentLocale());
    }

    public function testSetLocale(): void
    {
        $this->localizationService->setLocale('en');
        $this->assertEquals('en', $this->localizationService->getCurrentLocale());
    }

    public function testGetSupportedLocales(): void
    {
        $supportedLocales = $this->localizationService->getSupportedLocales();
        
        $this->assertIsArray($supportedLocales);
        $this->assertContains('fr', $supportedLocales);
        $this->assertContains('en', $supportedLocales);
        $this->assertContains('es', $supportedLocales);
        $this->assertContains('de', $supportedLocales);
        $this->assertContains('it', $supportedLocales);
        $this->assertContains('pt', $supportedLocales);
        $this->assertContains('ar', $supportedLocales);
        $this->assertContains('zh', $supportedLocales);
        $this->assertCount(8, $supportedLocales);
    }

    public function testIsLocaleSupported(): void
    {
        $this->assertTrue($this->localizationService->isLocaleSupported('fr'));
        $this->assertTrue($this->localizationService->isLocaleSupported('en'));
        $this->assertFalse($this->localizationService->isLocaleSupported('xx'));
    }

    public function testTranslateWithExistingKey(): void
    {
        $this->localizationService->setLocale('fr');
        $translation = $this->localizationService->translate('error.csrf_invalid');
        
        $this->assertIsString($translation);
        $this->assertNotEmpty($translation);
    }

    public function testTranslateWithNonExistentKey(): void
    {
        $this->localizationService->setLocale('fr');
        $translation = $this->localizationService->translate('non.existent.key');
        
        $this->assertEquals('non.existent.key', $translation);
    }

    public function testTranslateWithParameters(): void
    {
        $this->localizationService->setLocale('fr');
        $translation = $this->localizationService->translate('error.file_too_large', ['size' => '3GB']);
        
        $this->assertIsString($translation);
        $this->assertStringContainsString('3GB', $translation);
    }

    public function testTranslateInDifferentLanguages(): void
    {
        // Test French
        $this->localizationService->setLocale('fr');
        $frTranslation = $this->localizationService->translate('error.csrf_invalid');
        
        // Test English
        $this->localizationService->setLocale('en');
        $enTranslation = $this->localizationService->translate('error.csrf_invalid');
        
        $this->assertNotEquals($frTranslation, $enTranslation);
        $this->assertNotEmpty($frTranslation);
        $this->assertNotEmpty($enTranslation);
    }

    public function testDetectLocaleFromAcceptLanguage(): void
    {
        $detected = $this->localizationService->detectLocaleFromAcceptLanguage('en-US,en;q=0.9,fr;q=0.8');
        $this->assertEquals('en', $detected);
        
        $detected = $this->localizationService->detectLocaleFromAcceptLanguage('fr-FR,fr;q=0.9');
        $this->assertEquals('fr', $detected);
        
        $detected = $this->localizationService->detectLocaleFromAcceptLanguage('de-DE,de;q=0.9');
        $this->assertEquals('de', $detected);
        
        $detected = $this->localizationService->detectLocaleFromAcceptLanguage('xx-XX,xx;q=0.9');
        $this->assertEquals('fr', $detected); // fallback to default
    }

    public function testSetUnsupportedLocale(): void
    {
        $this->localizationService->setLocale('xx');
        $this->assertEquals('fr', $this->localizationService->getCurrentLocale()); // should fallback to default
    }

    public function testGetAvailableLanguages(): void
    {
        $languages = $this->localizationService->getAvailableLanguages();
        
        $this->assertIsArray($languages);
        $this->assertArrayHasKey('fr', $languages);
        $this->assertArrayHasKey('en', $languages);
        $this->assertEquals('Français', $languages['fr']);
        $this->assertEquals('English', $languages['en']);
    }
}