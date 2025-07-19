# Application de Partage de Fichiers

## 🚀 Code de l'Application

Ce dossier contient uniquement le code utile de l'application de partage de fichiers.

### 📁 Structure

```
app/
├── src/                    # Code source PHP
│   ├── Controllers/        # Contrôleurs MVC
│   ├── Http/              # Classes de réponse HTTP
│   ├── Models/            # Modèles de données
│   ├── Security/          # Classes de sécurité
│   ├── Services/          # Services métier
│   └── Views/             # Vues HTML
├── public/                # Point d'entrée web
│   ├── index.php          # Routeur principal
│   ├── css/               # Styles CSS
│   └── js/                # JavaScript
├── translations/          # Fichiers de traduction (8 langues)
├── data/                  # Base de données SQLite
├── uploads/               # Fichiers uploadés (créé automatiquement)
├── composer.json          # Dépendances PHP
└── vendor/                # Dépendances installées (généré)
```

## 🏃‍♂️ Démarrage Rapide

1. **Installation des dépendances** :
   ```bash
   cd app/
   composer install
   ```

2. **Permissions** :
   ```bash
   chmod 755 uploads data
   ```

3. **Lancement** :
   ```bash
   php -S localhost:8000 -t public/
   ```

4. **Accès** : http://localhost:8000

## 🔧 Configuration

- **Upload maximal** : 3GB
- **Langues supportées** : FR, EN, ES, DE, IT, PT, AR, ZH
- **Base de données** : SQLite (`data/files.db`)
- **Stockage** : Local (`uploads/`)

## 📝 Fonctionnalités

- ✅ Upload par glisser-déposer
- ✅ URLs courtes pour partage
- ✅ Suivi des fichiers par cookies
- ✅ Suppression sécurisée
- ✅ Interface multilingue
- ✅ Page 404 personnalisée
- ✅ Protection CSRF
- ✅ Validation MIME

## 🛠️ Développement

Pour les tests et outils de développement, voir le dossier `../dev-tools/`.
Pour la documentation complète, voir le dossier `../docs/`.