#!/usr/bin/env php
<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Security\SecurityChecker;

class SecurityConsole
{
    private SecurityChecker $checker;

    public function __construct()
    {
        $this->checker = new SecurityChecker();
    }

    public function run(): void
    {
        $this->printHeader();
        
        while (true) {
            $this->printMenu();
            $choice = $this->getInput("Choisissez une option (1-7, q pour quitter): ");
            
            switch ($choice) {
                case '1':
                    $this->checkDirectoryPermissions();
                    break;
                case '2':
                    $this->checkFileAccess();
                    break;
                case '3':
                    $this->checkUploadSecurity();
                    break;
                case '4':
                    $this->checkDirectoryTraversal();
                    break;
                case '5':
                    $this->generateFullReport();
                    break;
                case '6':
                    $this->runAllChecks();
                    break;
                case '7':
                    $this->runLiveScan();
                    break;
                case 'q':
                case 'Q':
                    echo "Au revoir!\n";
                    exit(0);
                default:
                    echo "Option invalide.\n\n";
            }
        }
    }

    private function printHeader(): void
    {
        echo "\n🔒 Console de Sécurité - PHP File Upload Service\n";
        echo "================================================\n\n";
    }

    private function printMenu(): void
    {
        echo "Options disponibles:\n";
        echo "1. Vérifier les permissions des dossiers\n";
        echo "2. Vérifier l'accès aux fichiers\n";
        echo "3. Vérifier la sécurité des uploads\n";
        echo "4. Tester le directory traversal\n";
        echo "5. Générer un rapport complet\n";
        echo "6. Lancer tous les tests\n";
        echo "7. Scan en temps réel\n";
        echo "q. Quitter\n\n";
    }

    private function getInput(string $prompt): string
    {
        echo $prompt;
        return trim(fgets(STDIN) ?: '');
    }

    private function checkDirectoryPermissions(): void
    {
        echo "\n📁 Vérification des permissions des dossiers...\n";
        echo "===========================================\n\n";

        $results = $this->checker->checkDirectoryPermissions();
        
        foreach ($results as $dir => $info) {
            $status = $info['exists'] ? '✅' : '❌';
            $writable = $info['writable'] ? 'W' : '-';
            $readable = $info['readable'] ? 'R' : '-';
            $permissions = $info['permissions'] ?? 'unknown';
            
            echo sprintf("%s %s: %s%s (%s)\n", $status, $dir, $readable, $writable, $permissions);
            
            if (!$info['exists']) {
                echo "   ⚠️  Dossier manquant!\n";
            }
        }
        
        echo "\n";
        $this->waitForEnter();
    }

    private function checkFileAccess(): void
    {
        echo "\n🔍 Vérification de l'accès aux fichiers...\n";
        echo "=====================================\n\n";

        $results = $this->checker->checkFileAccess();
        
        echo sprintf("Fichiers PHP hors public: %s\n", 
            $results['php_files_outside_public']['accessible'] ? '❌ Accessibles' : '✅ Protégés'
        );
        
        echo sprintf("Fichiers de config: %s\n", 
            $results['config_files_accessible']['accessible'] ? '❌ Accessibles' : '✅ Protégés'
        );

        if (isset($results['sensitive_files'])) {
            echo "\nFichiers sensibles détectés:\n";
            foreach ($results['sensitive_files'] as $file => $info) {
                $webAccess = $info['accessible_via_web'] ? '❌ Web' : '✅ Local';
                echo sprintf("  %s: %s\n", $file, $webAccess);
            }
        }

        echo "\n";
        $this->waitForEnter();
    }

    private function checkUploadSecurity(): void
    {
        echo "\n🛡️ Vérification de la sécurité des uploads...\n";
        echo "=========================================\n\n";

        $results = $this->checker->checkUploadSecurity();
        
        echo sprintf("Protection .htaccess: %s\n", 
            $results['htaccess_protection'] ? '✅ Activée' : '❌ Manquante'
        );
        
        echo sprintf("Exécution PHP bloquée: %s\n", 
            $results['php_execution_blocked'] ? '✅ Bloquée' : '❌ Autorisée'
        );

        if ($results['htaccess_content']) {
            echo "\nContenu .htaccess:\n";
            echo "----------------\n";
            echo $results['htaccess_content'] . "\n";
        }

        echo "\n";
        $this->waitForEnter();
    }

    private function checkDirectoryTraversal(): void
    {
        echo "\n🔄 Test de directory traversal...\n";
        echo "=============================\n\n";

        $results = $this->checker->checkDirectoryTraversal();
        
        echo sprintf("Statut de sécurité: %s\n", 
            $results['safe'] ? '✅ Sûr' : '❌ Vulnérable'
        );

        if (!empty($results['vulnerable_paths'])) {
            echo "\nChemins vulnérables détectés:\n";
            foreach ($results['vulnerable_paths'] as $path) {
                echo sprintf("  ⚠️  %s\n", $path);
            }
        }

        echo "\n";
        $this->waitForEnter();
    }

    private function generateFullReport(): void
    {
        echo "\n📊 Génération du rapport complet...\n";
        echo "==============================\n\n";

        $report = $this->checker->generateSecurityReport();
        echo $report;

        echo "\n🎯 Résumé:\n";
        echo "--------\n";
        $hasIssues = $this->checker->hasSecurityIssues();
        echo sprintf("Problèmes de sécurité détectés: %s\n", 
            $hasIssues ? '❌ Oui' : '✅ Non'
        );

        echo "\n";
        $this->waitForEnter();
    }

    private function runAllChecks(): void
    {
        echo "\n🔍 Exécution de tous les tests de sécurité...\n";
        echo "=========================================\n\n";

        $this->checkDirectoryPermissions();
        $this->checkFileAccess();
        $this->checkUploadSecurity();
        $this->checkDirectoryTraversal();
        $this->generateFullReport();
    }

    private function runLiveScan(): void
    {
        echo "\n🔴 Scan en temps réel (Ctrl+C pour arrêter)...\n";
        echo "=========================================\n\n";

        $lastCheck = 0;
        $interval = 5; // secondes

        while (true) {
            if (time() - $lastCheck >= $interval) {
                system('clear');
                echo "🔴 SCAN EN TEMPS RÉEL - " . date('H:i:s') . "\n";
                echo "================================\n\n";
                
                $hasIssues = $this->checker->hasSecurityIssues();
                echo sprintf("Statut: %s\n\n", 
                    $hasIssues ? '❌ PROBLÈMES DÉTECTÉS' : '✅ SÉCURISÉ'
                );
                
                if ($hasIssues) {
                    echo $this->checker->generateSecurityReport();
                }
                
                $lastCheck = time();
            }
            
            sleep(1);
        }
    }

    private function waitForEnter(): void
    {
        echo "Appuyez sur Entrée pour continuer...";
        fgets(STDIN);
    }
}

// Lancer la console
$console = new SecurityConsole();
$console->run();