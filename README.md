# 📁 Projet de Partage de Fichiers

Structure organisée du projet séparant clairement le code utile des outils de développement.

## 🗂️ Organisation

### 🚀 [`app/`](./app/) - **Application Production**
Code source complet de l'application prêt pour la production :
- Code PHP MVC complet
- Interface web responsive
- 8 langues supportées
- Configuration et dépendances

👉 **[Voir le README de l'application](./app/README.md)**

### 📚 [`docs/`](./docs/) - **Documentation**
- `CLAUDE.md` - Guide pour Claude Code
- `README.md` - Documentation générale
- `LICENCE.md` - Licence du projet
- Guides d'installation et d'utilisation

### 🧪 [`dev-tools/`](./dev-tools/) - **Outils de Développement**
- Tests unitaires PHPUnit
- Script de test d'intégration (`test_server.sh`)
- Scanners de sécurité
- Configuration de tests

### 🗃️ [`uploads/`](./uploads/) - **Données Utilisateur**
Fichiers uploadés par les utilisateurs (données temporaires)

## 🏃‍♂️ Démarrage Rapide

```bash
cd app/
composer install
php -S localhost:8000 -t public/
```

## 🧪 Tests

```bash
cd dev-tools/
./test_server.sh  # Test d'intégration complet
phpunit           # Tests unitaires
```

## 🎯 Utilisation

- **Développement** : Travaillez dans `app/`
- **Tests** : Utilisez `dev-tools/`
- **Documentation** : Consultez `docs/`
- **Production** : Déployez uniquement `app/`