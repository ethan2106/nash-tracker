<?php

/**
 * Page Profil utilisateur
 * - Affichage des stats, mesures, objectifs
 * - Graphiques et activités récentes
 * - Score santé NAFLD.
 */

declare(strict_types=1);

require_once __DIR__ . '/../Helper/view_helpers.php';

// LES DONNÉES SONT PRÉPARÉES PAR LE CONTROLLER QUI APPELLE CETTE VUE

$title = 'Profil - Suivi Nash';

ob_start();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/js/components/alpine-profile.js?v=<?= time(); ?>"></script>

<!-- Hero Section avec Avatar et Stats Rapides -->
<div class="min-h-screen"
     x-data="profileManager()"
     data-stats='<?= htmlspecialchars(json_encode([
         'imc' => $stats['imc'] ?? 0,
         'calories_target' => $stats['calories_target'] ?? 0,
         'objectifs_completion' => $stats['objectifs_completion'] ?? 0,
         'score' => $realScore ?? 0,
     ]), ENT_QUOTES); ?>'
     data-activities='<?= htmlspecialchars(json_encode($recentActivities), ENT_QUOTES); ?>'
     data-current-page='<?= $recentPage; ?>'
     data-total='<?= $recentTotal; ?>'
     data-weekly-nutrition='<?= htmlspecialchars(json_encode($weeklyNutrition ?? []), ENT_QUOTES); ?>'
     data-level='<?= htmlspecialchars(json_encode($levelData), ENT_QUOTES); ?>'
     x-init="init()">
    <div class="max-w-7xl mx-auto px-6 py-8">
        
        <!-- Header Hero -->
        <?php require __DIR__ . '/components/profile/header-hero.php'; ?>
        
        <?php if ($user && $objectifs)
        { ?>
            
            <!-- Section Mesures et Objectifs Nutritionnels -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <?php require __DIR__ . '/components/profile/mesures-card.php'; ?>
                <?php require __DIR__ . '/components/profile/objectifs-nutrition-card.php'; ?>
                <?php require __DIR__ . '/components/profile/score-card.php'; ?>
            </div>
            
            <!-- Graphiques et Activités Récentes -->
            <?php require __DIR__ . '/components/profile/graphiques-section.php'; ?>
            
            <!-- Actions et Conseils -->
            <?php require __DIR__ . '/components/profile/actions-section.php'; ?>
        
        <?php } else
        { ?>
            
            <!-- Empty State: Pas d'objectifs configurés -->
            <?php require __DIR__ . '/components/profile/empty-state.php'; ?>
        
        <?php } ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
