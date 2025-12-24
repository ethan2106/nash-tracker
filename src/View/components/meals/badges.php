<?php

/**
 * Composant: Gamification Repas (Streak + Badge).
 *
 * @description Affiche la progression gamifiée pour la nutrition
 * @var int   $streakNutrition   Jours consécutifs de suivi
 * @var array $badgesNutrition   Badges {earned: array, toEarn: array}
 *
 * @note Simplifié pour l'instant - à améliorer avec historique réel
 */

declare(strict_types=1);

// Variables attendues du parent (avec valeurs par défaut)
$streakNutrition = $streakNutrition ?? 0;
$badgesNutrition = $badgesNutrition ?? ['earned' => [], 'toEarn' => []];
$hasEarnedBadges = !empty($badgesNutrition['earned']);
?>
<!-- ============================================================
     GAMIFICATION NUTRITION
     - Affichage conditionnel (seulement si streak > 0 ou badges)
     - Fond dégradé violet (thème repas)
     ============================================================ -->
<?php if ($streakNutrition > 0 || $hasEarnedBadges)
{ ?>
<div class="mb-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl shadow-lg p-4 text-white">
    <div class="flex items-center justify-between flex-wrap gap-4">
        
        <!-- ===== STREAK: Jours de suivi consécutifs ===== -->
        <?php if ($streakNutrition > 0)
        { ?>
        <div class="flex items-center gap-3">
            <div class="text-3xl">🔥</div>
            <div>
                <div class="text-xl font-bold"><?= $streakNutrition; ?> jour<?= $streakNutrition > 1 ? 's' : ''; ?></div>
                <div class="text-purple-100 text-sm">de suivi nutritionnel</div>
            </div>
        </div>
        <?php } ?>

        <!-- ===== BADGES GAGNÉS ===== -->
        <?php if ($hasEarnedBadges)
        { ?>
        <div class="flex items-center gap-2">
            <?php foreach ($badgesNutrition['earned'] as $badge)
            { ?>
            <div class="flex items-center gap-2 bg-white/20 rounded-full px-3 py-1" 
                 title="<?= htmlspecialchars($badge['description'] ?? '', ENT_QUOTES); ?>">
                <span class="text-xl"><?= $badge['icon'] ?? '🏅'; ?></span>
                <span class="text-sm font-medium"><?= htmlspecialchars($badge['label'] ?? '', ENT_QUOTES); ?></span>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>
<?php } ?>
