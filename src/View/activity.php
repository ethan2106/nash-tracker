<?php

/**
 * Page Activités Physiques
 * - Suivi des activités et calories brûlées
 * - Calculs basés sur le système MET médical
 * - Gamification : streak, badges activité.
 */

declare(strict_types=1);

$sessionPath = __DIR__ . '/../Config/session.php';
if (file_exists($sessionPath))
{
    require_once $sessionPath;
}

// Service Container
require_once __DIR__ . '/../Service/ServiceContainer.php';
require_once __DIR__ . '/../Service/GamificationService.php';
require_once __DIR__ . '/../Config/DIContainer.php';

$title = 'Activités Physiques - Suivi Nash';

/** @var array|null $user */
$user = $_SESSION['user'] ?? null;

require_once __DIR__ . '/../Controller/ActivityController.php';

use App\Controller\ActivityController;
use App\Service\GamificationService;
use App\Service\ServiceContainer;

// Récupération des données
$container = \App\Config\DIContainer::getContainer();
$activityController = $container->get(ActivityController::class);
$activitesData = $activityController->getActivitesAujourdhui();
$historiqueData = $activityController->getHistoriqueActivites();

/** @var array $activites */
$activites = ($activitesData['success'] ?? false) ? $activitesData['activites'] : [];
/** @var int $totalCalories */
$totalCalories = ($activitesData['success'] ?? false) ? (int)$activitesData['total_calories'] : 0;
/** @var float|null $userWeight */
$userWeight = ($activitesData['success'] ?? false) ? $activitesData['user_weight'] : null;
/** @var array $historique */
$historique = ($historiqueData['success'] ?? false) ? $historiqueData['historique'] : [];

// Gamification - Calcul du streak activité
$gamification = ServiceContainer::make(GamificationService::class);

// Streak basé sur les jours avec activité
$streakActivity = $gamification->computeStreak(
    $historique,
    fn (array $jour): bool => ($jour['total_calories'] ?? 0) > 0
);

// Score hebdomadaire activité
$weeklyScoreActivity = $gamification->computeWeeklyScore(
    $historique,
    fn (array $jour): bool => ($jour['total_calories'] ?? 0) >= 200 // Objectif: 200 cal/jour
);

// Badges activité (génériques streak)
$badgesActivity = $gamification->computeStreakBadges($streakActivity, count(array_filter(
    $historique,
    fn (array $jour): bool => ($jour['total_calories'] ?? 0) > 0
)), 'activite');

ob_start();
?>

<!-- ============================================================
     PAGE ACTIVITÉS PHYSIQUES
     - Container principal avec Alpine.js (x-data="activityManager")
     - Data attributes pour passer les données PHP → JS
     ============================================================ -->
<div class="min-h-screen"
     x-data="activityManager"
     data-activites='<?= htmlspecialchars(json_encode($activites), ENT_QUOTES); ?>'
     data-total-calories="<?= $totalCalories; ?>"
     data-historique='<?= htmlspecialchars(json_encode($historique), ENT_QUOTES); ?>'
     data-user-profile='<?= htmlspecialchars(json_encode([
         'weight' => $userWeight,
         'age' => $user['annee'] ?? null,
         'gender' => $user['sexe'] ?? null,
     ]), ENT_QUOTES); ?>'
     data-streak="<?= $streakActivity; ?>"
     data-weekly-score='<?= htmlspecialchars(json_encode($weeklyScoreActivity), ENT_QUOTES); ?>'
     data-badges-earned='<?= htmlspecialchars(json_encode($badgesActivity['earned']), ENT_QUOTES); ?>'
     data-badges-to-earn='<?= htmlspecialchars(json_encode($badgesActivity['toEarn']), ENT_QUOTES); ?>'>

    <!-- ==================== NOTIFICATIONS TOAST ==================== -->
    <?php require __DIR__ . '/components/activity/notifications.php'; ?>

    <div class="max-w-[1280px] mx-auto px-6 py-8">
        
        <!-- ==================== HEADER + TOOLTIP MET ==================== -->
        <?php require __DIR__ . '/components/activity/header.php'; ?>

        <!-- ==================== GAMIFICATION (Streak + Badges) ==================== -->
        <?php require __DIR__ . '/components/activity/badges.php'; ?>

        <!-- ==================== GRILLE PRINCIPALE (3 colonnes) ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- COLONNE GAUCHE: Formulaire d'ajout (1/3) -->
            <?php require __DIR__ . '/components/activity/add-form.php'; ?>

            <!-- MODAL: Calculateur MET (hors flux, position fixed) -->
            <?php require __DIR__ . '/components/activity/met-calculator-modal.php'; ?>

            <!-- COLONNE DROITE: Données du jour + Historique (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Résumé: Total calories + Nombre activités -->
                <?php require __DIR__ . '/components/activity/today-summary.php'; ?>

                <!-- Liste des activités du jour -->
                <?php require __DIR__ . '/components/activity/today-list.php'; ?>

                <!-- Historique 7 derniers jours -->
                <?php require __DIR__ . '/components/activity/history.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     DONNÉES GLOBALES JAVASCRIPT
     - Exposées sur window pour Alpine.js
     - JSON_HEX_APOS | JSON_HEX_QUOT: Échappe les apostrophes/guillemets
     ============================================================ -->
<script>
// Activités enregistrées aujourd'hui (array)
window.todaysActivities = <?= json_encode($activites, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Total calories brûlées aujourd'hui (int)
window.totalCalories = <?= $totalCalories; ?>;

// Historique des 7 derniers jours (array)
window.historyData = <?= json_encode($historique, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Gamification: streak en jours (int)
window.activityStreak = <?= $streakActivity; ?>;

// Gamification: badges earned/toEarn (object)
window.activityBadges = <?= json_encode($badgesActivity, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
<script src="/js/components/alpine-activity.js"></script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
