# CLAUDE.md

Ce fichier fournit des directives à Claude Code (claude.ai/code) pour travailler avec le code de ce dépôt.

## Aperçu du Projet

Il s'agit d'un service PHP de téléchargement et de partage de fichiers qui suit les principes SOLID et les normes PSR-12. Il offre des téléchargements de fichiers par glisser-déposer avec raccourcissement d'URL et suivi des utilisateurs via des cookies.

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
- **Views** (`src/Views/`): Templates HTML

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
- **Base de Données**: SQLite avec auto-migration pour les mises à jour de schéma

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

## Dépendances

- **PHP**: ≥8.0 avec extension PDO SQLite
- **Composer**: Autoload PSR-4 avec mapping namespace `App\` vers `src/`
- **Frontend**: JavaScript vanilla avec glisser-déposer et barres de progression

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