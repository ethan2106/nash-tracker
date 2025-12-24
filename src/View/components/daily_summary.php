<?php
/**
 * Composant: Bilan nutritionnel du jour
 * Affiche un aperçu visuel des macronutriments consommés.
 */
$dashboard = $viewData['dashboard'] ?? [];
$objectifs = $dashboard['objectifs'] ?? null;
$stats = $dashboard['stats'] ?? [];

if ($objectifs)
{
    // Récupération des valeurs réelles depuis stats
    $caloriesConsumed = $stats['calories_consumed'] ?? 0;
    $proteinesConsumed = $stats['proteines_consumed'] ?? 0;
    $glucidesConsumed = $stats['glucides_consumed'] ?? 0;
    $lipidesConsumed = $stats['lipides_consumed'] ?? 0;

    $caloriesTarget = $objectifs['calories_perte'] ?? 1800;
    $proteinesTarget = $objectifs['proteines_max'] ?? 90;
    $glucidesTarget = 250; // ~50% des calories en glucides (estimation)
    $lipidesTarget = 60;   // ~30% des calories en lipides (estimation)

    // Calcul des pourcentages
    $caloriesPct = $caloriesTarget > 0 ? min(100, ($caloriesConsumed / $caloriesTarget) * 100) : 0;
    $proteinesPct = $proteinesTarget > 0 ? min(100, ($proteinesConsumed / $proteinesTarget) * 100) : 0;
    $glucidesPct = $glucidesTarget > 0 ? min(100, ($glucidesConsumed / $glucidesTarget) * 100) : 0;
    $lipidesPct = $lipidesTarget > 0 ? min(100, ($lipidesConsumed / $lipidesTarget) * 100) : 0;

    // Détermination du statut glucides
    $glucidesStatus = 'Ajoutez repas';
    $glucidesColor = 'slate';
    if ($glucidesConsumed > 0)
    {
        if ($glucidesPct < 80)
        {
            $glucidesStatus = 'Insuffisant';
            $glucidesColor = 'orange';
        } elseif ($glucidesPct > 120)
        {
            $glucidesStatus = 'Élevé';
            $glucidesColor = 'red';
        } else
        {
            $glucidesStatus = 'Optimal';
            $glucidesColor = 'green';
        }
    }

    // Détermination du statut lipides
    $lipidesStatus = 'Ajoutez repas';
    $lipidesColor = 'slate';
    if ($lipidesConsumed > 0)
    {
        if ($lipidesPct < 70)
        {
            $lipidesStatus = 'Faible';
            $lipidesColor = 'blue';
        } elseif ($lipidesPct > 130)
        {
            $lipidesStatus = 'Élevé';
            $lipidesColor = 'red';
        } else
        {
            $lipidesStatus = 'Bon';
            $lipidesColor = 'green';
        }
    }
    ?>
<div class="bg-gradient-to-br from-slate-50 to-blue-50 rounded-2xl p-6 border border-slate-200" x-show="isLoaded" x-transition>
    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
        <i class="fa-solid fa-chart-pie text-blue-600 mr-2" aria-hidden="true"></i>
        Bilan nutritionnel
    </h3>
    
    <div class="grid grid-cols-2 gap-4">
        <!-- Calories -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-green-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-slate-600">Calories</span>
                <i class="fa-solid fa-fire text-green-500" aria-hidden="true"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900"><?= round($caloriesConsumed); ?></p>
            <p class="text-xs text-slate-500 mt-1">sur <?= round($caloriesTarget); ?> kcal</p>
            <div class="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-green-400 to-green-600 rounded-full transition-all duration-1000" 
                     style="width: <?= round($caloriesPct); ?>%"></div>
            </div>
        </div>

        <!-- Protéines -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-purple-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-slate-600">Protéines</span>
                <i class="fa-solid fa-dumbbell text-purple-500" aria-hidden="true"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900"><?= round($proteinesConsumed); ?>g</p>
            <p class="text-xs text-slate-500 mt-1">sur <?= round($proteinesTarget); ?>g</p>
            <div class="mt-3 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-400 to-purple-600 rounded-full transition-all duration-1000" 
                     style="width: <?= round($proteinesPct); ?>%"></div>
            </div>
        </div>

        <!-- Glucides -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-orange-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-slate-600">Glucides</span>
                <i class="fa-solid fa-wheat-awn text-orange-500" aria-hidden="true"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900"><?= round($glucidesConsumed); ?>g</p>
            <p class="text-xs text-slate-500 mt-1">sur ~<?= round($glucidesTarget); ?>g</p>
            <div class="mt-3 flex gap-1">
                <span class="inline-block px-2 py-1 bg-<?= $glucidesColor; ?>-100 text-<?= $glucidesColor; ?>-700 text-xs rounded-full">
                    <i class="fa-solid fa-circle text-xs" aria-hidden="true"></i> <?= $glucidesStatus; ?>
                </span>
            </div>
        </div>

        <!-- Lipides -->
        <div class="bg-white rounded-xl p-4 shadow-sm border border-yellow-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-slate-600">Lipides</span>
                <i class="fa-solid fa-droplet text-yellow-500" aria-hidden="true"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900"><?= round($lipidesConsumed); ?>g</p>
            <p class="text-xs text-slate-500 mt-1">sur ~<?= round($lipidesTarget); ?>g</p>
            <div class="mt-3 flex gap-1">
                <span class="inline-block px-2 py-1 bg-<?= $lipidesColor; ?>-100 text-<?= $lipidesColor; ?>-700 text-xs rounded-full">
                    <i class="fa-solid fa-circle text-xs" aria-hidden="true"></i> <?= $lipidesStatus; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Conseil rapide -->
    <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-200">
        <p class="text-xs text-blue-800 flex items-start">
            <i class="fa-solid fa-lightbulb text-blue-600 mr-2 mt-0.5" aria-hidden="true"></i>
            <span>
                <?php if ($caloriesConsumed == 0)
                { ?>
                    Ajoutez vos repas pour voir votre progression nutritionnelle en temps réel
                <?php } elseif ($caloriesPct < 50)
                { ?>
                    Vous êtes en dessous de votre objectif calorique. Pensez à ajouter un en-cas équilibré.
                <?php } elseif ($caloriesPct > 110)
                { ?>
                    Objectif calorique dépassé. Privilégiez les légumes et protéines maigres pour le prochain repas.
                <?php } else
                { ?>
                    Bonne progression ! Continuez avec des repas équilibrés riches en fibres.
                <?php } ?>
            </span>
        </p>
    </div>
</div>
<?php } ?>
