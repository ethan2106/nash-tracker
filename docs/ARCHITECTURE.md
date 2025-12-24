# Suivi Nash - Architecture du Projet

> Application de suivi nutritionnel et santé pour patients NASH  
> Stack : PHP 8.3 + Tailwind CSS + Alpine.js

---

## 📁 Structure des Dossiers

```
site4/
├── public/              # Point d'entrée web
│   ├── index.php        # Front controller unique
│   ├── css/
│   │   ├── style.css    # Tailwind compilé
│   │   └── tailwind.css # Source Tailwind
│   └── js/components/   # Scripts JS spécifiques
│
├── src/
│   ├── Config/
│   │   └── session.php  # Gestion session PHP
│   │
│   ├── Controller/      # Logique métier
│   │   ├── BaseApiController.php  # Classe parent API
│   │   ├── FoodController.php
│   │   ├── EauController.php
│   │   ├── ActivityController.php
│   │   ├── MealController.php
│   │   ├── ProfileController.php
│   │   └── ...
│   │
│   ├── Model/           # Accès base de données
│   │   ├── database.php          # Connexion PDO
│   │   ├── DatabaseWrapper.php   # Wrapper requêtes
│   │   ├── UserModel.php
│   │   ├── EauModel.php
│   │   ├── MealModel.php
│   │   └── ...
│   │
│   ├── Service/         # Logique réutilisable
│   │   ├── FoodQualityService.php
│   │   ├── NutritionService.php
│   │   ├── DashboardService.php
│   │   └── ...
│   │
│   ├── Helper/          # Fonctions utilitaires
│   │   ├── view_helpers.php
│   │   ├── validation.php
│   │   └── ResponseHelper.php
│   │
│   └── View/            # Templates PHP
│       ├── layout.php   # Layout principal (head, body, scripts)
│       ├── home.php     # Dashboard utilisateur
│       ├── login.php
│       ├── register.php
│       └── components/  # Composants réutilisables
│           ├── header.php        # Navigation principale + mobile
│           ├── footer.php
│           ├── alert.php
│           ├── form-input.php
│           ├── food/
│           ├── meals/
│           ├── eau/
│           ├── activity/
│           ├── profile/
│           ├── settings/
│           ├── imc/
│           └── catalog/
│
├── routes/              # Définition des routes
│   ├── food.php
│   ├── eau.php
│   ├── activity.php
│   └── ...
│
├── storage/cache/       # Cache applicatif
├── tests/               # Tests PHPUnit
└── vendor/              # Dépendances Composer
```

---

## 🔄 Flux d'une Requête

```
1. public/index.php (Front Controller)
   ↓
2. routes/*.php (Routing par ?page=xxx)
   ↓
3. src/Controller/*Controller.php
   ↓
4. src/Model/*.php (BDD) + src/Service/*.php (Logique)
   ↓
5. src/View/*.php + components/ (Rendu HTML)
```

---

## 🎨 Stack Frontend

### Tailwind CSS
- **Config** : `tailwind.config.js`
- **Source** : `public/css/tailwind.css`
- **Compilé** : `public/css/style.css`
- **Build** : `npm run build` (ou watch)

### Alpine.js
- Chargé via CDN dans `layout.php`
- Plugins : `focus`, `collapse`
- Utilisé pour : modals, dropdowns, formulaires dynamiques

### Design System
- **Glassmorphism** : `bg-white/70 backdrop-blur-xl rounded-3xl`
- **Couleurs** :
  - Vert = Activité physique
  - Bleu = Eau / Général
  - Orange = Catalogue
  - Purple = Repas
  - Rouge = Alertes / Déconnexion
- **Coins** : `rounded-xl` (boutons), `rounded-3xl` (cards)

---

## 🧩 Composants Clés

### header.php (Navigation)
- Desktop : Dropdowns avec hover + click
- Mobile : Menu hamburger overlay plein écran
- JavaScript intégré pour toggle

### layout.php
- Meta tags, Tailwind, Alpine.js
- Skip link accessibilité
- Gradient de fond global
- Footer inclus

### form-input.php
- Composant générique pour inputs
- Génère automatiquement label + input + validation

---

## 🗄️ Base de Données

Tables principales :
- `users` - Comptes utilisateurs
- `foods` / `user_foods` - Aliments
- `meals` - Repas journaliers
- `eau_entries` - Suivi hydratation
- `activities` - Activités physiques
- `medicaments` / `prises_medicament` - Médicaments
- `historique_mesures` - Poids/IMC historique
- `objectifs` - Objectifs personnalisés

---

## 🔐 Sécurité

- Sessions PHP avec tokens CSRF
- `htmlspecialchars()` systématique
- Prepared statements PDO
- Validation côté serveur (Helper/validation.php)

---

## 📱 Responsive

- Breakpoint principal : `lg` (1024px)
- Mobile-first avec Tailwind
- Menu hamburger < 1024px
- Navigation desktop >= 1024px

---

## 🧪 Tests

```bash
# Lancer les tests
composer test
# ou
./vendor/bin/phpunit
```

---

## 🚀 Commandes Utiles

```bash
# Tailwind watch
npm run watch

# Tailwind build prod
npm run build

# PHPStan (analyse statique)
composer phpstan (niveau 5 recommandé)

# Tests
composer test
```

---

## 📝 Conventions

1. **Nommage** : PascalCase (classes), camelCase (méthodes), snake_case (BDD)
2. **Views** : Un dossier par feature dans `components/`
3. **CSS** : Classes Tailwind inline, pas de CSS custom sauf exceptions
4. **JS** : Alpine.js préféré, vanilla JS si nécessaire (header.php)
5. **Icônes** : FontAwesome avec `aria-hidden="true"`
