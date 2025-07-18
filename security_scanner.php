#!/usr/bin/env php
<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

class SecurityScanner
{
    private string $baseUrl;
    private array $vulnerabilityTests = [];

    public function __construct(string $baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->initializeTests();
    }

    private function initializeTests(): void
    {
        $this->vulnerabilityTests = [
            'directory_traversal' => [
                '/../../../etc/passwd',
                '/../composer.json',
                '/../src/Controllers/HomeController.php',
                '/../../CLAUDE.md',
                '/../vendor/autoload.php',
            ],
            'source_code_exposure' => [
                '/src/Controllers/HomeController.php',
                '/composer.json',
                '/CLAUDE.md',
                '/.env',
                '/tests/SecurityCheckerTest.php',
            ],
            'sensitive_files' => [
                '/.git/config',
                '/.env',
                '/config.php',
                '/database.php',
                '/admin.php',
            ],
            'upload_bypass' => [
                '/uploads/../composer.json',
                '/uploads/../src/Controllers/HomeController.php',
                '/uploads/../../etc/passwd',
            ],
        ];
    }

    public function runSecurityScan(): array
    {
        $results = [];
        
        echo "🔍 Démarrage du scan de sécurité...\n";
        echo "================================\n\n";

        foreach ($this->vulnerabilityTests as $category => $tests) {
            echo "🔎 Test: " . ucfirst(str_replace('_', ' ', $category)) . "\n";
            $results[$category] = $this->runCategoryTests($category, $tests);
            echo "\n";
        }

        return $results;
    }

    private function runCategoryTests(string $category, array $tests): array
    {
        $results = [];
        
        foreach ($tests as $test) {
            $url = $this->baseUrl . $test;
            $result = $this->testUrl($url);
            
            $results[$test] = $result;
            
            $status = $result['vulnerable'] ? '❌ VULNÉRABLE' : '✅ SÉCURISÉ';
            $httpCode = $result['http_code'];
            
            echo sprintf("  %s: %s (HTTP %d)\n", $test, $status, $httpCode);
            
            if ($result['vulnerable'] && $result['content_preview']) {
                echo sprintf("    📄 Contenu exposé: %s\n", substr($result['content_preview'], 0, 100) . '...');
            }
        }
        
        return $results;
    }

    private function testUrl(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'SecurityScanner/1.0',
            CURLOPT_HEADER => false,
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $vulnerable = $this->isVulnerable($httpCode, $content, $url);
        
        return [
            'url' => $url,
            'http_code' => $httpCode,
            'vulnerable' => $vulnerable,
            'content_preview' => $vulnerable ? substr($content, 0, 200) : null,
            'error' => $error,
        ];
    }

    private function isVulnerable(int $httpCode, $content, string $url): bool
    {
        // HTTP 200 pour des fichiers qui ne devraient pas être accessibles
        if ($httpCode !== 200) {
            return false;
        }

        // Vérification du contenu pour détecter l'exposition de code source
        if (is_string($content)) {
            // Détection de code PHP
            if (str_contains($content, '<?php')) {
                return true;
            }
            
            // Détection de configuration sensible
            if (str_contains($content, 'password') || 
                str_contains($content, 'secret') || 
                str_contains($content, 'key')) {
                return true;
            }
            
            // Détection de contenu JSON/YAML de configuration
            if ((str_contains($url, 'composer.json') || str_contains($url, '.env')) && 
                (str_contains($content, '"') || str_contains($content, '='))) {
                return true;
            }
        }

        return false;
    }

    public function generateReport(array $results): string
    {
        $report = "🔒 RAPPORT DE SÉCURITÉ\n";
        $report .= "=====================\n\n";
        
        $totalVulnerabilities = 0;
        $totalTests = 0;
        
        foreach ($results as $category => $tests) {
            $categoryVulns = 0;
            $categoryTests = count($tests);
            
            foreach ($tests as $test) {
                $totalTests++;
                if ($test['vulnerable']) {
                    $categoryVulns++;
                    $totalVulnerabilities++;
                }
            }
            
            $categoryName = ucfirst(str_replace('_', ' ', $category));
            $report .= sprintf("📂 %s: %d/%d vulnérabilités\n", 
                $categoryName, $categoryVulns, $categoryTests);
        }
        
        $report .= "\n🎯 RÉSUMÉ GLOBAL\n";
        $report .= "===============\n";
        $report .= sprintf("Total des vulnérabilités: %d/%d\n", $totalVulnerabilities, $totalTests);
        $report .= sprintf("Niveau de sécurité: %s\n", 
            $totalVulnerabilities === 0 ? '✅ EXCELLENT' : 
            ($totalVulnerabilities <= 2 ? '⚠️ ACCEPTABLE' : '❌ CRITIQUE')
        );
        
        if ($totalVulnerabilities > 0) {
            $report .= "\n🚨 ACTIONS RECOMMANDÉES\n";
            $report .= "======================\n";
            $report .= "1. Vérifier la configuration du serveur web\n";
            $report .= "2. Ajouter/vérifier les fichiers .htaccess\n";
            $report .= "3. Configurer les règles de réécriture\n";
            $report .= "4. Limiter l'accès aux fichiers sensibles\n";
        }
        
        return $report;
    }

    public function testSpecificVulnerability(string $vulnerability): void
    {
        echo "🔍 Test spécifique: $vulnerability\n";
        echo "================================\n\n";
        
        $testUrls = [
            'path_traversal' => [
                $this->baseUrl . '/../../../etc/passwd',
                $this->baseUrl . '/../composer.json',
                $this->baseUrl . '/uploads/../../../etc/passwd',
            ],
            'source_exposure' => [
                $this->baseUrl . '/src/Controllers/HomeController.php',
                $this->baseUrl . '/composer.json',
                $this->baseUrl . '/CLAUDE.md',
            ],
            'config_exposure' => [
                $this->baseUrl . '/.env',
                $this->baseUrl . '/config.php',
                $this->baseUrl . '/.git/config',
            ],
        ];
        
        if (!isset($testUrls[$vulnerability])) {
            echo "❌ Test non reconnu: $vulnerability\n";
            return;
        }
        
        foreach ($testUrls[$vulnerability] as $url) {
            $result = $this->testUrl($url);
            $status = $result['vulnerable'] ? '❌ VULNÉRABLE' : '✅ SÉCURISÉ';
            
            echo sprintf("%s: %s (HTTP %d)\n", $url, $status, $result['http_code']);
            
            if ($result['vulnerable'] && $result['content_preview']) {
                echo "Contenu exposé:\n";
                echo "---------------\n";
                echo substr($result['content_preview'], 0, 500) . "...\n\n";
            }
        }
    }
}

// Interface CLI
if ($argc < 2) {
    echo "Usage: php security_scanner.php <command> [options]\n";
    echo "Commands:\n";
    echo "  scan [url]     - Scan complet (défaut: http://localhost:8000)\n";
    echo "  test <vuln>    - Test spécifique (path_traversal, source_exposure, config_exposure)\n";
    echo "  help           - Afficher cette aide\n";
    exit(1);
}

$command = $argv[1];
$scanner = new SecurityScanner($argv[2] ?? 'http://localhost:8000');

switch ($command) {
    case 'scan':
        $results = $scanner->runSecurityScan();
        echo $scanner->generateReport($results);
        break;
        
    case 'test':
        if (!isset($argv[2])) {
            echo "❌ Veuillez spécifier le type de test\n";
            exit(1);
        }
        $scanner->testSpecificVulnerability($argv[2]);
        break;
        
    case 'help':
        echo "🔒 Scanner de Sécurité PHP\n";
        echo "=========================\n\n";
        echo "Ce scanner teste les vulnérabilités web courantes:\n";
        echo "- Directory traversal\n";
        echo "- Exposition de code source\n";
        echo "- Accès aux fichiers sensibles\n";
        echo "- Contournement des restrictions d'upload\n\n";
        echo "Utilisation:\n";
        echo "  php security_scanner.php scan\n";
        echo "  php security_scanner.php test path_traversal\n";
        break;
        
    default:
        echo "❌ Commande non reconnue: $command\n";
        echo "Utilisez 'help' pour voir les options disponibles.\n";
        exit(1);
}