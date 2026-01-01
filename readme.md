# Suivi Nash

## Description

Suivi Nash est une application web développée en PHP pour aider les utilisateurs à suivre leur santé et à gérer les risques liés à la stéatose hépatique non alcoolique (NAFLD, ou NASH en anglais). L'application permet de calculer l'IMC, de suivre les repas, les activités physiques, et d'obtenir un score de santé global basé sur ces données.

### Fonctionnalités principales
- **Calculateur NAFLD** : Outil pour évaluer les risques de stéatose hépatique.
- **Suivi des repas** : Enregistrement des aliments consommés avec calcul des valeurs nutritionnelles.
- **Activités physiques** : Suivi des exercices et de l'hydratation.
- **IMC et score santé** : Calcul automatique de l'indice de masse corporelle et d'un score de santé personnalisé.
- **Dashboard** : Vue d'ensemble des données avec graphiques et conseils.
- **Notifications** : Rappels personnalisés pour les repas et les activités.
- **API** : Endpoints pour intégrer avec d'autres services.

L'application utilise une base de données SQLite pour stocker les données utilisateur de manière sécurisée et locale.

## Installation

### Prérequis
- PHP 8.3 ou supérieur
- Composer
- Un serveur web comme Apache ou Nginx (recommandé avec Laragon sur Windows)
- SQLite

### Étapes d'installation
1. Clonez le dépôt :
   ```bash
   git clone https://github.com/votre-utilisateur/suivi-nash.git
   cd suivi-nash
   ```

2. Installez les dépendances PHP :
   ```bash
   composer install
   ```

3. Configurez l'environnement :
   - Copiez le fichier `.env.example` vers `.env` :
     ```bash
     cp .env.example .env
     ```
   - Ajustez les variables d'environnement si nécessaire (base de données, etc.).

4. Initialisez la base de données :
   - Les migrations sont dans le dossier `migrations/`.
   - Exécutez les migrations si nécessaire (l'application peut créer la base automatiquement).

5. Lancez le serveur :
   - Avec Laragon : Ouvrez le projet dans Laragon et démarrez le serveur.
   - Ou utilisez PHP intégré :
     ```bash
     php -S localhost:8000 -t public/
     ```
   - Accédez à `http://localhost:8000` dans votre navigateur.

## Utilisation

- **Inscription/Connexion** : Créez un compte pour commencer à suivre vos données.
- **Ajouter des repas** : Utilisez l'interface pour enregistrer vos aliments et portions.
- **Suivre les activités** : Enregistrez vos exercices physiques et votre hydratation.
- **Consulter le dashboard** : Visualisez vos progrès avec des graphiques et des scores.
- **Calculateur NAFLD** : Accédez à la page dédiée pour une évaluation rapide.

## Développement

### Commandes utiles
- `composer check:all` : Vérifie le style, PHPStan et lance PHPUnit.
- `composer csfix` : Corrige automatiquement le style de code.
- `composer stan` : Analyse statique avec PHPStan.
- `composer test` : Exécute les tests PHPUnit.

### Structure du projet
- `src/` : Code source (Contrôleurs, Modèles, Services, etc.).
- `tests/` : Tests unitaires et d'intégration.
- `public/` : Fichiers publics (index.php, CSS, JS).
- `migrations/` : Scripts SQL pour la base de données.
- `docs/` : Documentation supplémentaire.

### Tests
Lancez les tests avec :
```bash
composer test
```

### Contribution
Les contributions sont les bienvenues ! Veuillez suivre ces étapes :
1. Forkez le projet.
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonction`).
3. Commitez vos changements (`git commit -am 'Ajoute nouvelle fonctionnalité'`).
4. Poussez vers la branche (`git push origin feature/nouvelle-fonction`).
5. Ouvrez une Pull Request.

Assurez-vous que le code respecte les standards (PHP-CS-Fixer, PHPStan niveau 5).

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## Contact

Pour toute question ou suggestion, ouvrez une issue sur GitHub ou contactez l'équipe de développement.
