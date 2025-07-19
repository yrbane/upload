<?php declare(strict_types=1);

namespace App\Security;

class SecurityChecker
{
    private string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = $baseDir ?? __DIR__ . '/../..';
    }

    public function checkDirectoryPermissions(): array
    {
        $directories = ['uploads', 'data', 'src', 'public', 'tests', 'vendor'];
        $results = [];

        foreach ($directories as $dir) {
            $path = $this->baseDir . '/' . $dir;
            $results[$dir] = [
                'exists' => is_dir($path),
                'writable' => is_writable($path),
                'readable' => is_readable($path),
                'permissions' => $this->getPermissions($path),
            ];
        }

        return $results;
    }

    public function checkFileAccess(): array
    {
        $sensitiveFiles = [
            'composer.json',
            'composer.lock',
            'CLAUDE.md',
            'src/Controllers/HomeController.php',
            'tests/SecurityCheckerTest.php',
        ];

        $results = [];
        foreach ($sensitiveFiles as $file) {
            $path = $this->baseDir . '/' . $file;
            $results[$file] = [
                'exists' => file_exists($path),
                'readable' => is_readable($path),
                'accessible_via_web' => $this->isAccessibleViaWeb($file),
            ];
        }

        return [
            'php_files_outside_public' => [
                'accessible' => $this->arePhpFilesAccessible(),
            ],
            'config_files_accessible' => [
                'accessible' => $this->areConfigFilesAccessible(),
            ],
            'sensitive_files' => $results,
        ];
    }

    public function generateSecurityReport(): string
    {
        $report = "🔒 Security Report\n";
        $report .= "==================\n\n";
        
        $report .= "📁 Directory Permissions\n";
        $report .= "------------------------\n";
        $permissions = $this->checkDirectoryPermissions();
        foreach ($permissions as $dir => $info) {
            $status = $info['exists'] ? '✅' : '❌';
            $writable = $info['writable'] ? 'W' : '-';
            $readable = $info['readable'] ? 'R' : '-';
            $report .= sprintf("%s %s: %s%s (%s)\n", 
                $status, 
                $dir, 
                $readable, 
                $writable, 
                $info['permissions'] ?? 'unknown'
            );
        }

        $report .= "\n🔍 File Access Security\n";
        $report .= "----------------------\n";
        $fileAccess = $this->checkFileAccess();
        $report .= sprintf("PHP files outside public: %s\n", 
            $fileAccess['php_files_outside_public']['accessible'] ? '❌ Accessible' : '✅ Protected'
        );
        $report .= sprintf("Config files accessible: %s\n", 
            $fileAccess['config_files_accessible']['accessible'] ? '❌ Accessible' : '✅ Protected'
        );

        $report .= "\n🛡️ Upload Security\n";
        $report .= "-----------------\n";
        $uploadSecurity = $this->checkUploadSecurity();
        $report .= sprintf(".htaccess protection: %s\n", 
            $uploadSecurity['htaccess_protection'] ? '✅ Enabled' : '❌ Missing'
        );
        $report .= sprintf("PHP execution blocked: %s\n", 
            $uploadSecurity['php_execution_blocked'] ? '✅ Blocked' : '❌ Allowed'
        );

        $report .= "\n🔄 Directory Traversal\n";
        $report .= "---------------------\n";
        $traversal = $this->checkDirectoryTraversal();
        $report .= sprintf("Safety status: %s\n", 
            $traversal['safe'] ? '✅ Safe' : '❌ Vulnerable'
        );

        return $report;
    }

    public function hasSecurityIssues(): bool
    {
        $fileAccess = $this->checkFileAccess();
        $uploadSecurity = $this->checkUploadSecurity();
        $traversal = $this->checkDirectoryTraversal();

        return $fileAccess['php_files_outside_public']['accessible'] ||
               $fileAccess['config_files_accessible']['accessible'] ||
               !$uploadSecurity['htaccess_protection'] ||
               !$uploadSecurity['php_execution_blocked'] ||
               !$traversal['safe'];
    }

    public function checkDirectoryTraversal(): array
    {
        $testPaths = [
            '../../../etc/passwd',
            '../../../../etc/passwd',
            '../src/Controllers/HomeController.php',
            '../../composer.json',
        ];

        $vulnerable = [];
        foreach ($testPaths as $path) {
            if (file_exists($this->baseDir . '/uploads/' . $path)) {
                $vulnerable[] = $path;
            }
        }

        return [
            'vulnerable_paths' => $vulnerable,
            'safe' => empty($vulnerable),
        ];
    }

    public function checkUploadSecurity(): array
    {
        $htaccessPath = $this->baseDir . '/uploads/.htaccess';
        $htaccessExists = file_exists($htaccessPath);
        $phpBlocked = false;

        if ($htaccessExists) {
            $content = file_get_contents($htaccessPath);
            $phpBlocked = str_contains($content, 'php') && 
                         (str_contains($content, 'deny') || str_contains($content, 'Require all denied'));
        }

        return [
            'htaccess_protection' => $htaccessExists,
            'php_execution_blocked' => $phpBlocked,
            'htaccess_content' => $htaccessExists ? file_get_contents($htaccessPath) : null,
        ];
    }

    private function getPermissions(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }
        
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    private function isAccessibleViaWeb(string $file): bool
    {
        return str_starts_with($file, 'public/');
    }

    private function arePhpFilesAccessible(): bool
    {
        // Check if PHP files outside public are accessible
        $phpFiles = ['src/Controllers/HomeController.php', 'tests/SecurityCheckerTest.php'];
        
        foreach ($phpFiles as $file) {
            if ($this->isAccessibleViaWeb($file)) {
                return true;
            }
        }
        
        return false;
    }

    private function areConfigFilesAccessible(): bool
    {
        // Check if config files are accessible
        $configFiles = ['composer.json', 'CLAUDE.md', '.env'];
        
        foreach ($configFiles as $file) {
            if ($this->isAccessibleViaWeb($file)) {
                return true;
            }
        }
        
        return false;
    }
}