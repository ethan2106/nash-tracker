<?php

/**
 * Composant: Résumé du Jour.
 *
 * @description Affiche les KPIs du jour: calories brûlées + nombre d'activités
 * @requires Alpine.js - Variables: totalCalories, bonusCalories, todaysActivities
 */

declare(strict_types=1);
?>
<!-- ============================================================
     RÉSUMÉ DU JOUR
     - 2 colonnes: Calories brûlées | Nombre d'activités
     - Bonus calories affiché si > 0
     ============================================================ -->
<div class="bg-white rounded-3xl shadow-xl p-6">
    <h3 class="text-xl font-bold mb-4 text-gray-800">📊 Aujourd'hui</h3>
    <div class="grid grid-cols-2 gap-4">
        <div class="text-center">
            <div class="text-3xl font-bold text-green-600" x-text="totalCalories"></div>
            <div class="text-sm text-gray-600">Calories brûlées</div>
            <div x-show="bonusCalories > 0" class="text-xs text-emerald-600 font-semibold mt-1">
                (+<span x-text="bonusCalories"></span> bonus)
            </div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-blue-600" x-text="todaysActivities.length"></div>
            <div class="text-sm text-gray-600">Activités</div>
        </div>
    </div>
</div>
