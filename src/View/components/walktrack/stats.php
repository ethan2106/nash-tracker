<!-- ============================================================
     STATS DU JOUR WALKTRACK
     ============================================================ -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    
    <!-- Distance du jour -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-green-200 p-5">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fa-solid fa-route text-green-600"></i>
            </div>
            <span class="text-sm text-slate-500">Distance</span>
        </div>
        <div class="text-2xl font-bold text-slate-800" id="stat-distance">
            <?= number_format($totals['distance_km'], 2, ',', ' '); ?> <span class="text-base font-normal text-slate-500">km</span>
        </div>
    </div>
    
    <!-- Durée du jour -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-200 p-5">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fa-solid fa-clock text-blue-600"></i>
            </div>
            <span class="text-sm text-slate-500">Durée</span>
        </div>
        <div class="text-2xl font-bold text-slate-800" id="stat-duration">
            <?= $totals['duration_minutes']; ?> <span class="text-base font-normal text-slate-500">min</span>
        </div>
    </div>
    
    <!-- Calories -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-orange-200 p-5">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                <i class="fa-solid fa-fire-flame-curved text-orange-600"></i>
            </div>
            <span class="text-sm text-slate-500">Calories</span>
        </div>
        <div class="text-2xl font-bold text-slate-800" id="stat-calories">
            <?= $totals['calories']; ?> <span class="text-base font-normal text-slate-500">kcal</span>
        </div>
    </div>
    
    <!-- Nombre de marches -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-purple-200 p-5">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                <i class="fa-solid fa-shoe-prints text-purple-600"></i>
            </div>
            <span class="text-sm text-slate-500">Marches</span>
        </div>
        <div class="text-2xl font-bold text-slate-800" id="stat-count">
            <?= $totals['count']; ?>
        </div>
    </div>
</div>

<!-- Barre de progression objectif du jour -->
<?php if (!empty($progression['jour']))
{ ?>
<div class="mt-4 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-slate-600">
            <i class="fa-solid fa-bullseye text-green-500 mr-1"></i>
            Objectif du jour
        </span>
        <span class="text-sm text-slate-500" id="progression-jour-text">
            <?= number_format($progression['jour']['actuel'], 2, ',', ''); ?> / <?= $progression['jour']['objectif']; ?> km
        </span>
    </div>
    <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-green-400 to-emerald-500 rounded-full transition-all duration-500"
             id="progression-jour-bar"
             style="width: <?= min(100, $progression['jour']['pourcentage']); ?>%"></div>
    </div>
    <?php if ($progression['jour']['atteint'])
    { ?>
        <div class="mt-2 text-sm text-green-600 font-medium">
            <i class="fa-solid fa-check-circle mr-1"></i>
            Objectif atteint ! 🎉
        </div>
    <?php } ?>
</div>
<?php } ?>
