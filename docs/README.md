# Fun File Uploader

## Présentation
Ce projet propose un service d'upload et d'hébergement de fichiers sous PHP 8, respectant les principes SOLID et les standards PSR‑12. Il inclut :

- **Drag & Drop** ou sélection de fichier classique
- **Barre de progression** côté client pendant l'upload
- **Raccourcisseur d'URL** sécurisé par hash (SQLite ou MySQL)
- **Background aléatoire** via [Picsum](https://picsum.photos)
- **Suivi des uploads** via cookie pour retrouver facilement ses fichiers

## Fonctionnalités

1. **Upload simple** : drag & drop ou sélection via un bouton
2. **Progress bar** : retour visuel de l'avancement de l'upload
3. **Stockage** : abstraction StorageInterface (implémentation LocalStorage)
4. **Short URL** : génération d'un hash unique, stockage dans SQLite (ou MySQL)
5. **Téléchargement** : accès via `/f/{hash}` géré par un rewrite Apache/Nginx
6. **Suivi utilisateur** : cookie HTTP-only (30 jours) pour lister ses fichiers
7. **Design fun** : fond Picsum aléatoire et interface épurée

## Prérequis

- PHP ≥ 8.0
- Extension **pdo_sqlite** (ou **pdo_mysql** pour MySQL)
- Serveur web (Apache/Nginx) avec mod_rewrite ou règles équivalentes
- Composer

## Installation

```bash
# 1. Cloner le dépôt
git clone https://example.com/your-repo.git
cd your-repo

# 2. Installer les dépendances
composer install

# 3. Créer les dossiers nécessaires
mkdir -p uploads data

# 4. Définir les permissions (remplacez www-data si besoin)
sudo chown -R www-data:www-data uploads data
sudo chmod 755 uploads data

# 5. (Optionnel) Configurer SSL et HTTPS sur votre serveur web
```

## Configuration

- **Base URL** : dans `public/index.php` ou via une variable d'environnement, ajustez `$baseHost` pour correspondre à votre domaine.
- **Base de données** : le chemin SQLite par défaut est `data/files.db`. Pour MySQL, adaptez le DSN dans `src/UrlShortener.php` et créez la table :
  ```sql
  CREATE TABLE IF NOT EXISTS files (
    hash VARCHAR(12) PRIMARY KEY,
    path TEXT NOT NULL,
    created_at DATETIME NOT NULL
  ) ENGINE=InnoDB;
  ```

## Structure du projet

```
project/
├─ public/                # Racine web
│  ├─ .htaccess           # Règles rewrite
│  ├─ index.php           # Front-end
│  ├─ upload.php          # API d'upload
│  └─ f.php               # Endpoint de téléchargement
├─ src/                   # Classes PHP (PSR-4)
│  ├─ StorageInterface.php
│  ├─ LocalStorage.php
│  ├─ UrlShortener.php
│  ├─ CookieManager.php
│  └─ FileUploader.php
├─ data/                  # Base SQLite par défaut
│  └─ files.db            # Mapping hash → chemin
├─ uploads/               # Répertoire d'upload
└─ composer.json          # Autoload & dépendances
```

## Sécurité et bonnes pratiques

- **CSRF** : implémenter un token sur l'upload
- **Validation** : vérifier le type MIME et la taille (max 10 MiB)
- **HTTPS** : forcer HTTPS et cookies `Secure`
- **Antivirus** : scanner les fichiers si nécessaire
- **Limitation de taux** : prévenir les abus d'upload

## Extensions possibles

- Authentification + interface utilisateur
- Intégration cloud (S3, GCS)
- Nettoyage automatique des anciens fichiers
- Statistiques d'usage

## Licence

Ce projet est sous licence MIT. Consultez le fichier [LICENSE](LICENSE) pour plus de détails.
