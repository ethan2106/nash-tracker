# TODO Projet Nash-Tracker 1.7 - Amélioration de la couverture des tests

## Contexte
Audit réalisé : Couverture actuelle ~70% (114 tests existants, tous passant). Note : 7/10. Objectif : Atteindre 80–85% rapidement (gros gain fiabilité)

Cible 2 : 90% seulement après stabilisation (sinon tu vas tester du vent)

Focus : chemins critiques + erreurs + sécurité basique

Mesure : couverture sur src/ uniquement (pas vendor, pas views)

## Phase 0 — Mesurer proprement (1h)

- [ ] Installer/configurer couverture (Xdebug ou PCOV)
- [ ] Ajouter un filtre coverage (phpunit.xml) : inclure src/, exclure Views/, Config/, public/, migrations/
- [ ] Sortir un rapport HTML + un résumé console
- [ ] Lister Top 10 fichiers les moins couverts mais critiques

Sans ça, "90%" ne veut rien dire.

## Phase 1 — Quick wins : services "purs" (4–6h)

Tests unitaires sans DB / sans HTTP. Tu vises les branches (succès + erreurs).

1) CSRF (priorité 1)

   CsrfServiceTest

   - [ ] génération token
   - [ ] validation OK/KO
   - [ ] token absent/expiré (si tu gères TTL)
   - [ ] régénération après succès (si prévu)

2) Validation (priorité 1)

   ValidationServiceTest

   - [ ] cas valides
   - [ ] cas invalides (email, mdp trop court, champs requis)
   - [ ] messages d'erreurs cohérents

3) Cache (priorité 2)

   CacheServiceTest

   - [ ] hit/miss
   - [ ] TTL respecté (test via "clock" mock si possible, sinon TTL = petit + sleep minimal)
   - [ ] invalidation
   - [ ] fallback si cache down (si tu l'as)

4) Rate limit (priorité 2)

   RateLimitServiceTest

   - [ ] incrément
   - [ ] blocage après seuil
   - [ ] reset/expiration

Déjà là tu fais monter la couverture sans t'embarquer dans des mocks HTTP débiles.

## Phase 2 — Auth : tester le comportement, pas le "hash interne" (3–5h)

AuthServiceTest

- [ ] login OK (user existant + mdp OK)
- [ ] login KO (mdp KO / user inconnu)
- [ ] logout (session clear)
- [ ] "remember"/token si tu en as (validation + expiration)
- [ ] protection anti session fixation (si tu regénères l'id)

⚠️ Si ton auth est fortement couplée à $_SESSION / headers : tu testes via wrapper Session (recommandé) ou tu fais des tests d'intégration légers.

## Phase 3 — Intégration minimale (2–4h)

Ici tu veux prouver que l'appli "tient debout" sur les routes clés.

- Smoke tests routes (sans front)
  - [ ] GET /login => 200
  - [ ] POST /login => redirect attendu
  - [ ] GET /meals (auth) => 200
  - [ ] POST /meals/add => 302/200 selon design
- [ ] DB : utiliser SQLite memory si possible, sinon test DB dédiée + transactions rollback

"end-to-end" complet avec navigateur : pas pour la v1.7.

## Phase 4 — Ce que tu repousses (pas prioritaire / trop cher)

À déplacer dans un "Backlog" :

- [ ] GamificationServiceTest (utile mais pas critique)
- [ ] MedicamentServiceTest (critique seulement si tu fais des calculs dangereux / rappels essentiels)
- [ ] Tests "perf" (bench) → fais-le quand tu as une vraie baseline et un problème réel
- [ ] Tests "sécurité" type SQLi/XSS → en PHP, tu couvres surtout :
  - [ ] requêtes préparées (tests de repository)
  - [ ] output escaping côté templates (plus du QA que du test unitaire)

Contrôleurs / Repositories : règle simple

Ne vise pas 100% des controllers : c'est souvent du glue.

Priorité aux repositories qui font des requêtes compliquées / règles métier.

Repositories (si tu les as vraiment)

- [ ] MealRepositoryTest : requêtes + cas "vide"
- [ ] FoodRepositoryTest : recherche + tri + limites