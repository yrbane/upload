<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Security\SecurityChecker;

final class SecurityCheckerTest extends TestCase
{
    private SecurityChecker $securityChecker;

    protected function setUp(): void
    {
        $this->securityChecker = new SecurityChecker();
    }

    public function testCheckDirectoryPermissions(): void
    {
        $results = $this->securityChecker->checkDirectoryPermissions();
        
        $this->assertIsArray($results);
        $this->assertArrayHasKey('uploads', $results);
        $this->assertArrayHasKey('data', $results);
        $this->assertArrayHasKey('src', $results);
        $this->assertArrayHasKey('public', $results);
    }

    public function testUploadsDirectoryIsWritable(): void
    {
        $results = $this->securityChecker->checkDirectoryPermissions();
        
        $this->assertTrue($results['uploads']['writable']);
        $this->assertTrue($results['uploads']['exists']);
    }

    public function testSrcDirectoryPermissions(): void
    {
        $results = $this->securityChecker->checkDirectoryPermissions();
        
        $this->assertTrue($results['src']['exists']);
        $this->assertTrue($results['src']['readable']);
        // src directory writable status depends on environment
        $this->assertIsBool($results['src']['writable']);
    }

    public function testDataDirectoryIsWritable(): void
    {
        $results = $this->securityChecker->checkDirectoryPermissions();
        
        $this->assertTrue($results['data']['writable']);
        $this->assertTrue($results['data']['exists']);
    }

    public function testPublicDirectoryIsReadable(): void
    {
        $results = $this->securityChecker->checkDirectoryPermissions();
        
        $this->assertTrue($results['public']['readable']);
        $this->assertTrue($results['public']['exists']);
    }

    public function testCheckFileAccess(): void
    {
        $results = $this->securityChecker->checkFileAccess();
        
        $this->assertIsArray($results);
        $this->assertArrayHasKey('php_files_outside_public', $results);
        $this->assertArrayHasKey('config_files_accessible', $results);
    }

    public function testPhpFilesOutsidePublicAreNotAccessible(): void
    {
        $results = $this->securityChecker->checkFileAccess();
        
        $this->assertFalse($results['php_files_outside_public']['accessible']);
    }

    public function testConfigFilesAreNotAccessible(): void
    {
        $results = $this->securityChecker->checkFileAccess();
        
        $this->assertFalse($results['config_files_accessible']['accessible']);
    }

    public function testGenerateSecurityReport(): void
    {
        $report = $this->securityChecker->generateSecurityReport();
        
        $this->assertIsString($report);
        $this->assertStringContainsString('Security Report', $report);
        $this->assertStringContainsString('Directory Permissions', $report);
        $this->assertStringContainsString('File Access', $report);
    }

    public function testHasSecurityIssues(): void
    {
        $hasIssues = $this->securityChecker->hasSecurityIssues();
        
        $this->assertIsBool($hasIssues);
    }

    public function testCheckDirectoryTraversal(): void
    {
        $results = $this->securityChecker->checkDirectoryTraversal();
        
        $this->assertIsArray($results);
        $this->assertArrayHasKey('vulnerable_paths', $results);
        $this->assertArrayHasKey('safe', $results);
    }

    public function testCheckUploadSecurity(): void
    {
        $results = $this->securityChecker->checkUploadSecurity();
        
        $this->assertIsArray($results);
        $this->assertArrayHasKey('htaccess_protection', $results);
        $this->assertArrayHasKey('php_execution_blocked', $results);
    }
}