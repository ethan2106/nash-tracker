# TODO - Nettoyage du Code Legacy Nash-Tracker

## ✅ TERMINÉ - ImcController Refactoring

### Services créés :
- **ImcDataService** : Récupération et calcul des données IMC
- **ImcApiService** : Formatage des données pour les APIs chart
- **ImcSaveService** : Validation, normalisation et sauvegarde
- **ObjectifsRepository** : Découplage de la persistance

### Tests ajoutés :
- ImcDataServiceTest (100% coverage)
- ImcApiServiceTest (100% coverage)
- ImcSaveServiceTest (100% coverage)

### Améliorations :
- ✅ Injection de dépendances propre
- ✅ Séparation des responsabilités
- ✅ Tests unitaires robustes
- ✅ Architecture maintenable

---

## 🔄 EN COURS - Prochain contrôleur : FoodController

### Analyse préliminaire :
- Gère la logique métier de gestion alimentaire
- Probablement couplé aux modèles FoodModel, MealModel
- Nécessite séparation en services (Data, API, Save)
- Tests à créer pour prévenir les régressions

### Tâches à effectuer :
- [ ] Créer FoodDataService (récupération données nourriture)
- [ ] Créer FoodApiService (formatage APIs)
- [ ] Créer FoodSaveService (validation et sauvegarde)
- [ ] Créer repositories si nécessaire (FoodRepository, MealRepository)
- [ ] Refactoriser FoodController avec DI
- [ ] Créer tests unitaires complets
- [ ] Mettre à jour container.php
- [ ] Valider avec tous les tests

---

## 📋 CONTRÔLEURS RESTANTS À REFACTORISER

### Priorité Haute :
- [ ] **FoodController** ← PROCHAIN
- [ ] **ProfileController**
- [ ] **ReportsController**

### Priorité Moyenne :
- [ ] **ActivityController**
- [ ] **WalkTrackController**
- [ ] **MedicamentController**
- [ ] **SettingsController**

### Priorité Basse :
- [ ] **UserController**
- [ ] **HomeController**
- [ ] **MealController**

### Contrôleurs déjà vérifiés :
- [x] **ImcController** - TERMINÉ
- [x] **BaseApiController** - Classe abstraite, pas besoin de refactoring

---

## 📊 MÉTRIQUES DE PROGRÈS

- **Contrôleurs total** : 12
- **Contrôleurs terminés** : 1 (ImcController)
- **Contrôleurs restants** : 11
- **Progression** : 8.3%

---

## 🎯 OBJECTIFS GÉNÉRAUX

- Séparer la logique métier des contrôleurs vers des services
- Implémenter l'injection de dépendances partout
- Créer des tests unitaires complets (100% coverage business logic)
- Utiliser le pattern Repository pour découpler la persistance
- Maintenir la compatibilité API existante
- Améliorer la maintenabilité et testabilité du code