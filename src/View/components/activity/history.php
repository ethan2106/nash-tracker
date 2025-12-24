<?php

/**
 * Composant: Historique Activités (7 jours).
 *
 * @description Liste les activités des 7 derniers jours
 * @requires Alpine.js - Variable: history (array)
 * @requires Alpine.js - Méthode: formatDate()
 */

declare(strict_types=1);
?>
<!-- ============================================================
     HISTORIQUE DES 7 DERNIERS JOURS
     - Boucle Alpine.js sur history
     - Affiche: date, nb activités, calories, durée
     ============================================================ -->
<div class="bg-white rounded-3xl shadow-xl p-6">
    <h3 class="text-xl font-bold mb-4 text-gray-800">📈 Historique (7 jours)</h3>

    <div x-show="history.length === 0" class="text-center py-8 text-gray-500">
        <p>Aucun historique disponible</p>
    </div>

    <div x-show="history.length > 0" class="space-y-2">
        <template x-for="day in history" :key="day.date">
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <div>
                    <div class="font-medium" x-text="formatDate(day.date)"></div>
                    <div class="text-sm text-gray-600" x-text="day.nombre_activites + ' activité(s)'"></div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-green-600" x-text="day.total_calories + ' cal'"></div>
                    <div class="text-sm text-gray-600" x-text="day.total_duree + ' min'"></div>
                </div>
            </div>
        </template>
    </div>
</div>
