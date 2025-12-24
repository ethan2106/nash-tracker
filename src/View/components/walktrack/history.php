<!-- ============================================================
     HISTORIQUE 7 DERNIERS JOURS
     ============================================================ -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200 p-5">
    
    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-calendar-days text-blue-500"></i>
        Historique (7 jours)
    </h3>
    
    <div id="history-list" class="space-y-2">
        <?php if (!empty($historique))
        { ?>
            <?php foreach ($historique as $jour)
            { ?>
                <?php
                    $date = new DateTime($jour['date']);
                $isToday = $jour['date'] === date('Y-m-d');
                $isYesterday = $jour['date'] === date('Y-m-d', strtotime('-1 day'));
                ?>
                <div class="flex items-center justify-between p-3 rounded-xl <?= $isToday ? 'bg-green-50 border border-green-200' : 'bg-slate-50'; ?>">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg <?= $isToday ? 'bg-green-200' : 'bg-slate-200'; ?> flex items-center justify-center">
                            <span class="text-sm font-bold <?= $isToday ? 'text-green-700' : 'text-slate-600'; ?>">
                                <?= $date->format('d'); ?>
                            </span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-slate-700">
                                <?php if ($isToday)
                                { ?>
                                    Aujourd'hui
                                <?php } elseif ($isYesterday)
                                { ?>
                                    Hier
                                <?php } else
                                { ?>
                                    <?= strftime('%A', $date->getTimestamp()); ?>
                                <?php } ?>
                            </div>
                            <div class="text-xs text-slate-400">
                                <?= $jour['nombre_marches']; ?> marche<?= $jour['nombre_marches'] > 1 ? 's' : ''; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-slate-700">
                            <?= number_format($jour['total_km'], 1, ',', ''); ?> km
                        </div>
                        <div class="text-xs text-slate-400">
                            <?= $jour['total_calories']; ?> kcal
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } else
        { ?>
            <div class="text-center py-8 text-slate-400">
                <i class="fa-solid fa-shoe-prints text-3xl mb-2"></i>
                <p class="text-sm">Aucune marche enregistrée</p>
                <p class="text-xs">Commencez à marcher !</p>
            </div>
        <?php } ?>
    </div>
</div>
