<?php

/**
 * Composant: Total Journalier Nutrition.
 *
 * @description Affiche le récapitulatif nutritionnel complet du jour
 * @requires Alpine.js - Variables: objectifs
 * @requires Alpine.js - Méthodes: getCaloriesColor(), formatNumber(), etc.
 *
 * @var array $totals        Totaux nutritionnels du jour
 * @var array $objectifs     Objectifs de l'utilisateur
 */

declare(strict_types=1);

// Variables attendues du parent
$totals = $totals ?? [];
$objectifs = $objectifs ?? [];

// Extraction des valeurs avec defaults
$totalCalories = $totals['calories'] ?? 0;
$totalProteines = $totals['proteines'] ?? 0;
$totalGlucides = $totals['glucides'] ?? 0;
$totalLipides = $totals['lipides'] ?? 0;
$totalSucres = $totals['sucres'] ?? 0;
$totalFibres = $totals['fibres'] ?? 0;
$totalGraissesSat = $totals['graisses_sat'] ?? 0;
$sportCalories = $totals['sport_calories'] ?? 0;
$rawSportCalories = $totals['raw_sport_calories'] ?? 0;

// Calculs pour les stats
$calorieGoal = ($objectifs['calories_perte'] ?? 2000) + $sportCalories;
$calorieProgress = $totalCalories > 0 ? min(100, round(($totalCalories / $calorieGoal) * 100)) : 0;
?>
<!-- ============================================================
     TOTAL JOURNALIER NUTRITION
     - En-tête avec calories principales
     - Grille des 6 nutriments (Protéines, Glucides, Lipides, Fibres, Sucres, Graisses sat.)
     - Statistiques globales
     ============================================================ -->
<div class="mb-8 bg-gradient-to-br from-white via-blue-50/30 to-purple-50/30 backdrop-blur-sm rounded-3xl p-8 border border-white/60 shadow-xl">
    
    <!-- ===== EN-TÊTE: Titre + Calories totales ===== -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-br from-purple-500 to-pink-500 p-3 rounded-2xl shadow-lg">
                <i class="fa-solid fa-chart-line text-white text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Total Journalier</h2>
                <p class="text-gray-600">Apport nutritionnel total du jour</p>
            </div>
        </div>
        
        <!-- Bloc calories principal (côté droit) -->
        <div class="text-right">
            <!-- Calories consommées -->
            <div class="flex items-center justify-end gap-3 mb-2">
                <div class="text-right">
                    <div class="text-4xl font-black" :class="getCaloriesColor()" id="total-calories">
                        <?= formatNumber($totalCalories, 0); ?>
                    </div>
                    <div class="text-sm text-gray-500">kcal consommées</div>
                </div>
            </div>

            <!-- Objectif calorique avec sport -->
            <div x-show="objectifs && objectifs.calories_perte" class="mb-3">
                <div class="flex items-center justify-end gap-2">
                    <span class="text-sm text-gray-600">Objectif:</span>
                    <span class="px-3 py-1 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-800 rounded-full text-sm font-semibold border border-purple-200">
                        <span x-text="formatNumber(objectifs.calories_perte, 0)"></span> kcal
                        <?php if ($sportCalories > 0)
                        { ?>
                            <span class="text-blue-600 font-bold mx-1">+</span>
                            <span class="text-blue-600 font-bold"><?= formatNumber($sportCalories, 0); ?></span>
                            <span class="text-blue-600 font-bold mx-1">=</span>
                            <span class="font-bold" x-text="formatNumber((objectifs.calories_perte || 0) + <?= $sportCalories; ?>, 0)"></span> kcal
                        <?php } ?>
                    </span>
                </div>
            </div>

            <!-- Badge activité physique -->
            <?php if ($sportCalories > 0)
            { ?>
            <div class="mt-2 text-right">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-800 rounded-full text-sm font-medium border border-blue-200">
                    <i class="fa-solid fa-running"></i>
                    <span>Activité physique</span>
                    <span class="font-bold">+<?= formatNumber($sportCalories, 0); ?> kcal ajoutées</span>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- ===== GRILLE DES NUTRIMENTS (6 cards) ===== -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        
        <!-- Protéines -->
        <div class="bg-white/70 rounded-2xl p-4 border border-white/50 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-red-400 to-red-500 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-drumstick-bite text-white text-sm"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Protéines</span>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold" :class="getProteinesColor()" id="total-proteines">
                        <?= formatNumber($totalProteines, 1); ?>
                    </div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-red-400 to-red-500 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + Math.min(100, (<?= $totalProteines; ?> / (objectifs?.proteines_max || 150)) * 100) + '%'">
                </div>
            </div>
            <div class="text-xs text-gray-500 mt-1 flex justify-between">
                <span x-show="objectifs && objectifs.proteines_min" class="text-green-600">
                    Min: <span x-text="objectifs.proteines_min"></span>g
                </span>
                <span x-show="objectifs && objectifs.proteines_max" class="text-red-600">
                    Max: <span x-text="objectifs.proteines_max"></span>g
                </span>
            </div>
        </div>

        <!-- Glucides -->
        <div class="bg-white/70 rounded-2xl p-4 border border-white/50 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-bread-slice text-white text-sm"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Glucides</span>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold" :class="getGlucidesColor()" id="total-glucides">
                        <?= formatNumber($totalGlucides, 1); ?>
                    </div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-yellow-400 to-orange-500 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + Math.min(100, (<?= $totalGlucides; ?> / (objectifs?.glucides || 300)) * 100) + '%'">
                </div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                <span x-show="objectifs && objectifs.glucides" class="text-orange-600">
                    Objectif: <span x-text="objectifs.glucides"></span>g
                </span>
            </div>
        </div>

        <!-- Lipides -->
        <div class="bg-white/70 rounded-2xl p-4 border border-white/50 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-500 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-seedling text-white text-sm"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Lipides</span>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold" :class="getLipidesColor()" id="total-lipides">
                        <?= formatNumber($totalLipides, 1); ?>
                    </div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-green-400 to-green-500 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + Math.min(100, (<?= $totalLipides; ?> / (objectifs?.graisses_sat_max || 22)) * 100) + '%'">
                </div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                <span x-show="objectifs && objectifs.graisses_sat_max" class="text-red-600">
                    Max graisses sat.: <span x-text="objectifs.graisses_sat_max"></span>g
                </span>
            </div>
        </div>

        <!-- Fibres -->
        <div class="bg-white/70 rounded-2xl p-4 border border-white/50 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-leaf text-white text-sm"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Fibres</span>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold" :class="getFibresColor()" id="total-fibres">
                        <?= formatNumber($totalFibres, 1); ?>
                    </div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + Math.min(100, (<?= $totalFibres; ?> / (objectifs?.fibres_min || 25)) * 100) + '%'">
                </div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                <span x-show="objectifs && objectifs.fibres_min" class="text-green-600">
                    Min: <span x-text="objectifs.fibres_min"></span>g
                </span>
                <span x-show="objectifs && objectifs.fibres_max" class="text-red-600 ml-2">
                    Max: <span x-text="objectifs.fibres_max"></span>g
                </span>
            </div>
        </div>

        <!-- Sucres -->
        <div class="bg-white/70 rounded-2xl p-4 border border-white/50 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-pink-400 to-pink-500 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-candy-cane text-white text-sm"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Sucres</span>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold" :class="getSucresColor()" id="total-sucres">
                        <?= formatNumber($totalSucres, 1); ?>
                    </div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-pink-400 to-pink-500 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + Math.min(100, (<?= $totalSucres; ?> / (objectifs?.sucres_max || 50)) * 100) + '%'">
                </div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                <span x-show="objectifs && objectifs.sucres_max" class="text-red-600">
                    Max: <span x-text="objectifs.sucres_max"></span>g
                </span>
            </div>
        </div>

        <!-- Graisses Saturées -->
        <div class="bg-white/70 rounded-2xl p-4 border border-white/50 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-cheese text-white text-sm"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Graisses Sat.</span>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold" :class="getGraissesSatColor()" id="total-graisses-sat">
                        <?= formatNumber($totalGraissesSat, 1); ?>
                    </div>
                    <div class="text-xs text-gray-500">g</div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-purple-400 to-purple-500 h-2 rounded-full transition-all duration-300"
                     :style="'width: ' + Math.min(100, (<?= $totalGraissesSat; ?> / (objectifs?.graisses_sat_max || 22)) * 100) + '%'">
                </div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                <span x-show="objectifs && objectifs.graisses_sat_max" class="text-red-600">
                    Max: <span x-text="objectifs.graisses_sat_max"></span>g
                </span>
            </div>
        </div>

        <!-- Sodium (conditionnel) -->
        <div class="bg-white/70 rounded-2xl p-4 border border-white/50 shadow-sm hover:shadow-md transition-shadow" x-show="objectifs && objectifs.sodium_max">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-cyan-400 to-cyan-500 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-flask text-white text-sm"></i>
                    </div>
                    <span class="font-semibold text-gray-700">Sodium</span>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold text-gray-800" id="total-sodium">0.0</div>
                    <div class="text-xs text-gray-500">mg</div>
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-cyan-400 to-cyan-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                <span x-show="objectifs && objectifs.sodium_max" class="text-red-600">
                    Max: <span x-text="objectifs.sodium_max"></span>mg
                </span>
            </div>
        </div>

        <!-- Placeholder pour équilibrer la grille -->
        <div class="bg-transparent rounded-2xl p-4" x-show="!(objectifs && objectifs.sodium_max)"></div>
    </div>

    <!-- ===== STATISTIQUES GLOBALES ===== -->
    <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-2xl border border-green-200">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-chart-pie text-white"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800">État nutritionnel du jour</h4>
                    <p class="text-sm text-gray-600">Suivi de vos objectifs quotidiens</p>
                </div>
            </div>
            <div class="text-right">
                <div class="flex flex-col gap-2">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                        <i class="fa-solid fa-circle-check"></i>
                        Objectifs actifs
                    </span>
                    <span class="text-xs text-gray-500">Mise à jour en temps réel</span>
                </div>
            </div>
        </div>

        <!-- Mini-statistiques (3 colonnes) -->
        <div class="grid grid-cols-3 gap-4 pt-4 border-t border-green-200">
            <!-- Calories -->
            <div class="text-center">
                <div class="text-lg font-bold text-green-600" id="total-calories-mini">
                    <?= formatNumber($totalCalories, 0); ?>
                </div>
                <div class="text-xs text-gray-600">Calories</div>
                <div class="text-xs text-gray-400 mt-1">
                    / <?= formatNumber($calorieGoal, 0); ?>
                </div>
            </div>
            
            <!-- Progression -->
            <div class="text-center">
                <div class="text-lg font-bold text-blue-600"><?= $calorieProgress; ?>%</div>
                <div class="text-xs text-gray-600">Objectif calories</div>
                <div class="text-xs text-gray-400 mt-1">
                    <?= formatNumber($totalCalories, 0); ?>/<?= formatNumber($calorieGoal, 0); ?>
                </div>
            </div>
            
            <!-- Sport -->
            <div class="text-center">
                <div class="text-lg font-bold text-purple-600">
                    <?= $rawSportCalories > 0 ? formatNumber($rawSportCalories, 0) : '0'; ?>
                </div>
                <div class="text-xs text-gray-600">Sport total (kcal)</div>
            </div>
        </div>
    </div>
</div>
