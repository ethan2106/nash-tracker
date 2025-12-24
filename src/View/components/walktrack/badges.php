<!-- ============================================================
     BADGES & STREAK WALKTRACK
     ============================================================ -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200 p-6 mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        
        <!-- Streak -->
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-fire text-white text-2xl"></i>
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-800" id="streak-count"><?= $streak; ?></div>
                <div class="text-slate-500 text-sm">jours consécutifs</div>
            </div>
        </div>
        
        <!-- Badges earned -->
        <div class="flex flex-wrap items-center gap-2" id="badges-container">
            <?php if (!empty($badges['earned']))
            { ?>
                <?php foreach ($badges['earned'] as $badge)
                { ?>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-gradient-to-r from-yellow-100 to-amber-100 border border-yellow-300 shadow-sm" 
                         title="<?= htmlspecialchars($badge['tip'] ?? $badge['condition'] ?? ''); ?>">
                        <span class="text-lg"><?= $badge['icon'] ?? '🏆'; ?></span>
                        <span class="text-sm font-medium text-amber-700"><?= htmlspecialchars($badge['label'] ?? 'Badge'); ?></span>
                    </div>
                <?php } ?>
            <?php } else
            { ?>
                <div class="text-slate-400 text-sm italic">
                    <i class="fa-solid fa-medal mr-1"></i>
                    Marchez pour débloquer des badges !
                </div>
            <?php } ?>
        </div>
        
        <!-- Prochain badge -->
        <?php if (!empty($badges['toEarn']))
        { ?>
            <?php $nextBadge = $badges['toEarn'][0] ?? null; ?>
            <?php if ($nextBadge)
            { ?>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200">
                    <span class="text-lg opacity-50"><?= $nextBadge['icon'] ?? '🎯'; ?></span>
                    <span class="text-sm text-slate-500">
                        Prochain : <?= htmlspecialchars($nextBadge['label'] ?? ''); ?>
                        <span class="text-xs text-slate-400">(<?= $nextBadge['hint'] ?? ''; ?>)</span>
                    </span>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>
