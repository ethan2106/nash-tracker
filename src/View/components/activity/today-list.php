<?php

/**
 * Composant: Liste des Activités du Jour.
 *
 * @description Affiche toutes les activités enregistrées aujourd'hui
 * @requires Alpine.js - Variable: todaysActivities (array)
 * @requires Alpine.js - Méthodes: getActivityEmoji(), formatActivityType(), formatTime(), deleteActivity()
 */

declare(strict_types=1);
?>
<!-- ============================================================
     LISTE DES ACTIVITÉS DU JOUR
     - État vide: message d'encouragement
     - État rempli: cards avec emoji, type, durée, calories, heure
     - Bouton suppression par activité
     ============================================================ -->
<div class="bg-white rounded-3xl shadow-xl p-6">
    <h3 class="text-xl font-bold mb-4 text-gray-800">📝 Activités du jour</h3>

    <div x-show="todaysActivities.length === 0" class="text-center py-8 text-gray-500">
        <div class="text-6xl mb-4">🏃‍♂️</div>
        <p>Aucune activité enregistrée aujourd'hui</p>
        <p class="text-sm">Ajoutez votre première activité !</p>
    </div>

    <div x-show="todaysActivities.length > 0" class="space-y-3">
        <template x-for="activity in todaysActivities" :key="activity.id">
            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                <div class="flex items-center space-x-3">
                    <div class="text-2xl">
                        <span x-text="getActivityEmoji(activity.type_activite)"></span>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 capitalize" x-text="formatActivityType(activity.type_activite)"></div>
                        <div class="text-sm text-gray-600" x-text="activity.duree_minutes + ' minutes'"></div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <div class="font-bold text-green-600" x-text="activity.calories_depensees + ' cal'"></div>
                        <div class="text-xs text-gray-500" x-text="formatTime(activity.date_heure)"></div>
                    </div>
                    <button @click="deleteActivity(activity.id)"
                            class="text-red-500 hover:text-red-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>
