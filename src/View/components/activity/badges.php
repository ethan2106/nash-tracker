<?php

/**
 * Composant: Gamification Activités (Streak + Badges).
 *
 * @description Affiche la progression gamifiée de l'utilisateur
 * @var int    $streakActivity       Nombre de jours consécutifs d'activité
 * @var array  $weeklyScoreActivity  Score hebdomadaire {score: int, total: int}
 * @var array  $badgesActivity       Badges {earned: array, toEarn: array}
 *
 * @uses GamificationService::computeStreak()
 * @uses GamificationService::computeWeeklyScore()
 * @uses GamificationService::computeStreakBadges()
 */

declare(strict_types=1);

// Variables attendues du parent (avec valeurs par défaut sécurisées)
$streakActivity = $streakActivity ?? 0;
$weeklyScoreActivity = $weeklyScoreActivity ?? ['score' => 0, 'daysWithGoal' => 0, 'totalDays' => 7];
$badgesActivity = $badgesActivity ?? ['earned' => [], 'toEarn' => []];
?>
<!-- ============================================================
     BANDEAU GAMIFICATION
     - Fond dégradé vert (thème activité)
     - 3 sections: Streak | Score hebdo | Badges
     ============================================================ -->
<div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-3xl shadow-xl p-6 text-white mb-8">
    <div class="flex items-center justify-between flex-wrap gap-4">
        
        <!-- ===== STREAK: Jours consécutifs d'activité ===== -->
        <div class="flex items-center gap-4">
            <div class="text-5xl">🔥</div>
            <div>
                <div class="text-3xl font-bold"><?= $streakActivity; ?> jour<?= $streakActivity > 1 ? 's' : ''; ?></div>
                <div class="text-emerald-100">Série en cours</div>
            </div>
        </div>

        <!-- Score hebdomadaire -->
        <div class="flex items-center gap-4">
            <div class="text-5xl">📊</div>
            <div>
                <div class="text-2xl font-bold"><?= $weeklyScoreActivity['daysWithGoal'] ?? 0; ?>/<?= $weeklyScoreActivity['totalDays'] ?? 7; ?></div>
                <div class="text-emerald-100">Jours actifs cette semaine</div>
            </div>
        </div>

        <!-- Badges gagnés -->
        <?php if (!empty($badgesActivity['earned']))
        { ?>
        <div class="flex items-center gap-2">
            <?php foreach (array_slice($badgesActivity['earned'], 0, 3) as $badge)
            { ?>
            <div class="text-3xl" title="<?= htmlspecialchars($badge['label'] ?? '', ENT_QUOTES); ?>">
                <?= $badge['icon'] ?? '🏅'; ?>
            </div>
            <?php } ?>
            <?php if (count($badgesActivity['earned']) > 3)
            { ?>
            <div class="text-sm bg-white/20 px-2 py-1 rounded-full">
                +<?= count($badgesActivity['earned']) - 3; ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- Prochain badge -->
    <?php if (!empty($badgesActivity['toEarn']))
    {
        $nextBadge = $badgesActivity['toEarn'][0];
        ?>
    <div class="mt-4 pt-4 border-t border-white/20">
        <div class="flex items-center gap-2 text-sm text-emerald-100">
            <span>🎯 Prochain badge:</span>
            <span class="font-semibold text-white"><?= htmlspecialchars($nextBadge['label'] ?? '', ENT_QUOTES); ?></span>
            <span class="text-2xl"><?= $nextBadge['icon'] ?? '🏅'; ?></span>
        </div>
    </div>
    <?php } ?>
</div>
