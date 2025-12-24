# Tests — Notes & Stratégie

Ce dossier centralise les notes de tests (unitaires/intégration), règles et cas limites à surveiller.

## Outils & Commandes

```powershell
composer run test        # PHPUnit 12
composer run stan        # PHPStan lvl 5
composer run csfix:check # Style PSR-12 (dry run)
composer run check:all   # CS-Fixer (dry-run) + PHPStan + PHPUnit
```

## Structure des tests
- Un fichier par classe: `tests/[Classe]Test.php`
- Tests unitaires: logique métier (services, helpers)
- Tests d’intégration: contrôleurs/routes, flux principaux
- Données de test minimales, reproductibles (factories/helpers si besoin)

## Conventions
- Nommage: `testNomDuComportement_Contexte_RésultatAttendu()`
- Couverture cible ≥ 80% (priorité au code critique)
- Tests stables: éviter dépendances réseau/temps non contrôlées
- Mocks/Stubs: préférer injection de dépendances

## Cas limites (checklist)
- Dates/Heures: fuseau (Europe/Paris), "aujourd’hui" vs minuit, changement d’heure
- Repas: création avec date seule → heure courante; ajout aliment → bump `NOW()` (tri récents)
- Activités récentes: requête `UNION ALL` triée par datetime; limites/offset globaux
- Notifications: heures silencieuses, cooldown anti-spam, empilement toasts
- Sécurité: CSRF (formulaires/AJAX), `htmlspecialchars`, validation inputs
- JSON: corps invalide/malformed, headers manquants
- DB: erreurs PDO, transactions (objectifs versioning)
- Objectifs: versioning actif/inactif, requêtes temporelles `getByUserAtDate()`

## Astuces
- Préférer Data Providers pour variations (valeurs limites, null, grands nombres)
- Tester messages/erreurs retournés (contrôleurs JSON)
- Vérifier l’ordre global des listes (tri par datetime réel)

## Modèle de plan de tests
- Modèle réutilisable: [test-plan-template.md](./test-plan-template.md)
- Usage rapide:
	1) Dupliquer le fichier → `test-plan-[feature].md`
	2) Compléter Contexte, Stratégie, Cas, Acceptation
	3) Lier le plan dans le ticket/PR

