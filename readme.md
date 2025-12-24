# Suivi Nash — Guide Développeur (Windows / Laragon)

## Commandes Rapides
- `composer check:all`: vérifie style (dry-run), PHPStan (lvl 5), puis lance PHPUnit
- `composer csfix`: corrige automatiquement le style (PHP-CS-Fixer)
- `composer csfix:check`: vérifie le style sans modifier
- `composer stan`: analyse statique PHPStan niveau 5
- `composer test`: exécute PHPUnit

## Configuration
- Copie `.env.example` vers `.env` et ajuste les variables DB si nécessaire.
- Variables par défaut : localhost, db=suivi_nash, user=root, pass=''

## Évolutions techniques récentes
- Activités récentes: une seule requête `UNION ALL` triée par `date` (repas/eau/activités) pour un ordre cohérent Dashboard + Profil.
- Repas — heure réelle: création sans heure → heure courante; ajout d’aliment sur un repas du jour → `date_heure = NOW()` (fait remonter l’élément).
- Score santé: calculé côté service (`DashboardService::computeHealthScore`), la vue consomme uniquement les résultats.
- Notifications: préférences utilisateur, heures silencieuses et anti-spam (cooldown) implémentés.
- Helpers UI: `getScoreTailwindColor`, IMC points + conseils.

## Lancer les vérifications
```powershell
# Dans C:\laragon\www\site4
composer run check:all
```

## Prérequis
- PHP 8.3 (Laragon)
- Composer
- SQLite (base de données incluse)

## Notes
- Les données actuelles sont de test. Pas besoin de migrer les repas horodatés à 00:00:00 — les nouveaux prennent l’heure réelle.
- Si nécessaire, corriger manuellement:
```sql
UPDATE repas SET date_heure = NOW()
WHERE user_id = VOTRE_USER_ID AND DATE(date_heure) = CURDATE() AND TIME(date_heure) = '00:00:00';
```
