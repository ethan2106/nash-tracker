<?php
/**
 * Card Objectifs Nutritionnels - Barres de progression des macros.
 * @param array $currentNutrition - Nutrition actuelle du jour
 * @param array $objectifs - Objectifs de l'utilisateur
 */
?>
<div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl p-8 border border-white/50 hover:shadow-2xl transition-all duration-300">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
            <i class="fa-solid fa-utensils text-white text-xl"></i>
        </div>
        <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Objectifs Nutritionnels</h3>
    </div>
    <div class="space-y-4">
        <!-- Calories -->
        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl">
            <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-gray-700">Calories</span>
                <span class="text-lg font-bold text-green-600">
                    <?= formatNumber($currentNutrition['calories'], 0); ?> / <?= formatNumber($objectifs['calories_perte'], 0); ?> kcal
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <?php $caloriesProgress = calculateProgress($currentNutrition['calories'], $objectifs['calories_perte']); ?>
                <div class="progress-bar-fill h-3 rounded-full bg-gradient-to-r from-green-400 to-green-600 transition-all duration-1000" data-width="<?= $caloriesProgress; ?>" style="width: 0%;"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1"><?= formatNumber($caloriesProgress, 1); ?>% de l'objectif journalier</div>
        </div>
        
        <!-- Protéines -->
        <div class="p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl">
            <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-gray-700">Protéines</span>
                <span class="text-lg font-bold text-blue-600">
                    <?= formatNumber($currentNutrition['proteines'], 1); ?> / <?= $objectifs['proteines_max']; ?> g
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <?php $proteinesProgress = calculateProgress($currentNutrition['proteines'], $objectifs['proteines_max']); ?>
                <div class="progress-bar-fill h-3 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 transition-all duration-1000" data-width="<?= $proteinesProgress; ?>" style="width: 0%;"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1">Objectif: <?= $objectifs['proteines_min']; ?> - <?= $objectifs['proteines_max']; ?> g</div>
        </div>
        
        <!-- Fibres -->
        <div class="p-4 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl">
            <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-gray-700">Fibres</span>
                <span class="text-lg font-bold text-orange-600">
                    <?= formatNumber($currentNutrition['fibres'], 1); ?> / <?= $objectifs['fibres_max']; ?> g
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <?php $fibresProgress = calculateProgress($currentNutrition['fibres'], $objectifs['fibres_max']); ?>
                <div class="progress-bar-fill h-3 rounded-full bg-gradient-to-r from-orange-400 to-orange-600 transition-all duration-1000" data-width="<?= $fibresProgress; ?>" style="width: 0%;"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1">Objectif: <?= $objectifs['fibres_min']; ?> - <?= $objectifs['fibres_max']; ?> g</div>
        </div>
        
        <!-- Lipides -->
        <div class="p-4 bg-gradient-to-r from-purple-50 to-violet-50 rounded-xl">
            <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-gray-700">Lipides</span>
                <span class="text-lg font-bold text-purple-600">
                    <?= number_format($currentNutrition['graisses_sat'], 1, ',', ' '); ?> / 22 g
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <?php
                $lipidesProgress = 22 > 0 ? min(100, ($currentNutrition['graisses_sat'] / 22) * 100) : 0;
$barColor = $currentNutrition['graisses_sat'] > 22 ? 'from-red-400 to-red-600' : 'from-purple-400 to-purple-600';
?>
                <div class="progress-bar-fill h-3 rounded-full bg-gradient-to-r <?= $barColor; ?> transition-all duration-1000" data-width="<?= $lipidesProgress; ?>" style="width: 0%;"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1">Recommandation NAFLD: ≤ <?= htmlspecialchars($data['userConfig']['lipides_max_g'] ?? 22); ?> g/jour</div>
        </div>
    </div>
</div>
