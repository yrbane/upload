# CLAUDE.md

Ce fichier fournit des directives à Claude Code (claude.ai/code) pour travailler avec le code de ce dépôt.

## Aperçu du Projet

Il s'agit d'un service PHP de téléchargement et de partage de fichiers qui suit les principes SOLID et les normes PSR-12. Il offre des téléchargements de fichiers par glisser-déposer avec raccourcissement d'URL, suivi des utilisateurs via des cookies, et un support complet d'internationalisation pour 8 langues.

## Commandes de Développement

### Configuration et Dépendances
```bash
# Installer les dépendances
composer install

# Créer les répertoires requis
mkdir -p uploads data

# Définir les permissions (ajuster l'utilisateur/groupe si nécessaire)
sudo chown -R www-data:www-data uploads data
sudo chmod 755 uploads data
```

### Lancement de l'Application
- **Développement local**: Utiliser le serveur intégré PHP : `php -S localhost:8000 -t public/`
- **Production**: Configurer Apache/Nginx pour servir depuis le répertoire `public/` avec réécriture d'URL

### Validation
- **Vérification syntaxe PHP**: `php -l src/**/*.php`
- **Dépendances**: `composer validate`
- **Tests**: `vendor/bin/phpunit`

## Architecture

### Structure MVC
- **Controllers** (`src/Controllers/`): Gèrent les requêtes HTTP et coordonnent la logique métier
  - `HomeController`: Page principale avec liste des fichiers
  - `UploadController`: Traitement des téléchargements de fichiers
  - `FileController`: Téléchargement/service des fichiers
  - `DeleteController`: Suppression des fichiers
- **Models** (`src/Models/`): Logique métier et gestion des données
  - `FileUploader`: Orchestre le workflow de téléchargement
  - `UrlShortener`: Raccourcissement d'URL basé sur SQLite avec génération de hash
  - `LocalStorage`: Implémentation du stockage de fichiers
  - `CookieManager`: Suivi des utilisateurs via cookies HTTP-only
- **Services** (`src/Services/`): Logique métier et services applicatifs
  - `HomeService`: Préparation des données de la page d'accueil
  - `UploadService`: Validation et traitement des téléchargements
  - `FileService`: Préparation des téléchargements de fichiers
  - `DeleteService`: Suppression de fichiers avec autorisation
  - `LocalizationService`: Traduction et gestion des langues
- **Views** (`src/Views/`): Templates HTML avec support d'internationalisation

### Routage des Requêtes
Routeur simple dans `public/index.php` qui gère :
- `/` → Page d'accueil avec interface de téléchargement
- `/upload` (POST) → Traitement des téléchargements
- `/f/{hash}` → Téléchargement de fichier par URL courte
- `/delete` (POST) → Suppression de fichier

### Fonctionnalités Clés
- **Stockage de Fichiers**: Configurable via `StorageInterface` (défaut: `LocalStorage`)
- **Raccourcissement d'URL**: Hash 12 caractères → mapping SQLite dans `data/files.db`
- **Suivi Utilisateur**: Cookies HTTP-only suivent les fichiers téléchargés (expiration 30 jours)
- **Sécurité**: Tokens CSRF, limites de taille de fichier (3GB), validation MIME
- **Internationalisation**: Support de 8 langues avec détection automatique
- **Base de Données**: SQLite avec auto-migration pour les mises à jour de schéma
- **Couche de Services**: Architecture propre avec injection de dépendances

### Schéma de Base de Données
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

- **URL de Base**: Définie dans `HomeController::index()` ou via environnement
- **Répertoire de Téléchargement**: Défaut `uploads/`, configurable dans `LocalStorage`
- **Base de Données**: Défaut `data/files.db`, connexion SQLite dans `UrlShortener`
- **Limite Taille Fichier**: 3GB (définie dans `FileUploader::upload()`)
- **Localisation**: Fichiers de traduction dans le répertoire `translations/`
- **Détection de Langue**: Automatique via l'en-tête HTTP Accept-Language

## Dépendances

- **PHP**: ≥8.0 avec extension PDO SQLite
- **Composer**: Autoload PSR-4 avec mapping namespace `App\` vers `src/`
- **Frontend**: JavaScript vanilla avec glisser-déposer et barres de progression

## Internationalisation (i18n)

L'application supporte 8 langues avec détection automatique et gestion des traductions :

### Langues Supportées
- **Français** (`fr`) - Langue par défaut
- **Anglais** (`en`)
- **Espagnol** (`es`)
- **Allemand** (`de`)
- **Italien** (`it`)
- **Portugais** (`pt`)
- **Arabe** (`ar`) - Support droite-à-gauche
- **Chinois** (`zh`) - Chinois simplifié

### Système de Traduction
- **LocalizationService**: Gère les traductions et la détection de langue
- **Fichiers de Traduction**: Situés dans le répertoire `translations/` (ex: `fr.php`, `en.php`)
- **Détection Automatique**: Utilise l'en-tête HTTP Accept-Language pour les préférences linguistiques
- **Substitution de Paramètres**: Supporte les valeurs dynamiques dans les traductions (ex: `{size}`)
- **Fallback**: Retourne la clé de traduction si la traduction est manquante

### Structure des Fichiers de Traduction
```php
<?php declare(strict_types=1);
return [
    'app' => [
        'title' => 'Partage de Fichiers',
        'upload' => 'Télécharger',
        // ... plus de chaînes d'application
    ],
    'error' => [
        'file_not_found' => 'Fichier non trouvé',
        'file_too_large' => 'Le fichier est trop volumineux (max {size})',
        // ... plus de messages d'erreur
    ],
    'success' => [
        'upload_complete' => 'Téléchargement terminé avec succès',
        // ... plus de messages de succès
    ]
];
```

### Utilisation dans le Code
```php
// Dans les services
$message = $this->localizationService->translate('error.file_not_found');
$message = $this->localizationService->translate('error.file_too_large', ['size' => '3GB']);

// Dans les vues
<?= htmlspecialchars($translations['app']['title']) ?>
```

## Méthodologie de Développement

Ce projet suit les principes du **Développement Piloté par les Tests (TDD)** :

### Workflow TDD
1. **Écrire le Test d'Abord**: Créer ou modifier les tests unitaires dans le répertoire `tests/` avant d'implémenter toute nouvelle fonctionnalité
2. **Faire Passer le Test**: Implémenter le code minimal pour faire passer le test (solution rapide et sale)
3. **Refactoriser**: Nettoyer et améliorer le code tout en gardant les tests verts
4. **Commit**: Valider les changements avec un message descriptif

### Règles TDD
- **Pas de code de production** sans un test qui échoue d'abord
- **Tout nouveau code PHP** doit avoir des tests unitaires correspondants
- **Refactoriser uniquement** quand les tests passent
- **Lancer les tests fréquemment** pendant le développement

### Directives de Test
- Utiliser PHPUnit pour tous les tests unitaires
- Les fichiers de test suivent la convention de nommage `*Test.php`
- Mocker les dépendances externes (base de données, système de fichiers, HTTP)
- Tester les scénarios de succès et d'échec
- Viser une couverture de code élevée