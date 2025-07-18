# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

**Version française disponible**: [CLAUDE_FR.md](CLAUDE_FR.md)

## Project Overview

This is a PHP file upload and sharing service that follows SOLID principles and PSR-12 standards. It provides drag & drop file uploads with URL shortening and user tracking via cookies.

## Development Commands

### Setup and Dependencies
```bash
# Install dependencies
composer install

# Create required directories
mkdir -p uploads data

# Set permissions (adjust user/group as needed)
sudo chown -R www-data:www-data uploads data
sudo chmod 755 uploads data
```

### Running the Application
- **Local development**: Use PHP's built-in server: `php -S localhost:8000 -t public/`
- **Production**: Configure Apache/Nginx to serve from `public/` directory with URL rewriting

### Validation
- **PHP syntax check**: `php -l src/**/*.php`
- **Dependencies**: `composer validate`
- **Tests**: `vendor/bin/phpunit`

## Architecture

### MVC Structure
- **Controllers** (`src/Controllers/`): Handle HTTP requests and coordinate business logic
  - `HomeController`: Main page with file listing
  - `UploadController`: File upload processing  
  - `FileController`: File download/serving
  - `DeleteController`: File deletion
- **Models** (`src/Models/`): Business logic and data handling
  - `FileUploader`: Orchestrates upload workflow
  - `UrlShortener`: SQLite-based URL shortening with hash generation
  - `LocalStorage`: File storage implementation
  - `CookieManager`: User tracking via HTTP-only cookies
- **Views** (`src/Views/`): HTML templates

### Request Routing
Simple router in `public/index.php` handles:
- `/` → Home page with upload interface
- `/upload` (POST) → File upload processing
- `/f/{hash}` → File download by short URL
- `/delete` (POST) → File deletion

### Key Features
- **File Storage**: Configurable via `StorageInterface` (default: `LocalStorage`)
- **URL Shortening**: 12-character hash → SQLite mapping in `data/files.db`
- **User Tracking**: HTTP-only cookies track uploaded files (30-day expiry)
- **Security**: CSRF tokens, file size limits (3GB), MIME type validation
- **Database**: SQLite with auto-migration for schema updates

### Database Schema
```sql
CREATE TABLE files (
    hash TEXT PRIMARY KEY,
    path TEXT NOT NULL,
    filename TEXT NOT NULL,
    mime_type TEXT,
    created_at TEXT NOT NULL
);
```

## Configuration

- **Base URL**: Set in `HomeController::index()` or via environment
- **Upload Directory**: Default `uploads/`, configurable in `LocalStorage`
- **Database**: Default `data/files.db`, SQLite connection in `UrlShortener`
- **File Size Limit**: 3GB (defined in `FileUploader::upload()`)

## Dependencies

- **PHP**: ≥8.0 with PDO SQLite extension
- **Composer**: PSR-4 autoloading with `App\` namespace mapping to `src/`
- **Frontend**: Vanilla JavaScript with drag & drop and progress bars

## Development Methodology

This project follows **Test-Driven Development (TDD)** principles:

### TDD Workflow
1. **Write Test First**: Create or modify unit tests in `tests/` directory before implementing any new functionality
2. **Make Test Pass**: Implement minimal code to make the test pass (quick and dirty solution)
3. **Refactor**: Clean up and improve the code while keeping tests green
4. **Commit**: Commit changes with descriptive message

### TDD Rules
- **No production code** without a failing test first
- **All new PHP code** must have corresponding unit tests
- **Refactor only** when tests are passing
- **Run tests frequently** during development

### Testing Guidelines
- Use PHPUnit for all unit tests
- Test files follow `*Test.php` naming convention
- Mock external dependencies (database, filesystem, HTTP)
- Test both success and failure scenarios
- Aim for high code coverage