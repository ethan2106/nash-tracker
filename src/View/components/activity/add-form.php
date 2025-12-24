<?php

/**
 * Composant: Formulaire d'ajout d'activité.
 *
 * @description Permet d'ajouter une nouvelle activité physique
 * @requires Alpine.js - Variables: activity, loading, estimatedCalories, showCalculator
 * @requires Alpine.js - Méthodes: addActivity(), updateCaloriesEstimation(), getActivityAverages()
 *
 * @see /js/components/alpine-activity.js pour la logique JS
 */

declare(strict_types=1);
?>
<!-- ============================================================
     FORMULAIRE D'AJOUT D'ACTIVITÉ
     - Colonne gauche (1/3 de la grille)
     - Sélection activité, durée, calories
     - Estimation automatique basée sur MET
     ============================================================ -->
<div class="lg:col-span-1">
    <div class="bg-white rounded-3xl shadow-xl p-6">
        <!-- Header avec icône -->
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl text-white">
                <i class="fa-solid fa-plus text-xl" aria-hidden="true"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Ajouter une activité</h2>
        </div>

        <!-- Formulaire Alpine.js: @submit.prevent empêche le rechargement page -->
        <form @submit.prevent="addActivity" class="space-y-6">
            
            <!-- ===== CHAMP 1: Sélection du type d'activité ===== -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Activité</label>
                <select x-model="activity.type"
                        @change="updateCaloriesEstimation"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-400 focus:ring-2 focus:ring-green-200 transition-all">
                    <option value="">-- Choisir une activité --</option>
                    <option value="marche">🚶 Marche rapide</option>
                    <option value="course">🏃 Course</option>
                    <option value="velo">🚴 Vélo</option>
                    <option value="natation">🏊 Natation</option>
                    <option value="yoga">🧘 Yoga</option>
                    <option value="musculation">💪 Musculation</option>
                    <option value="danse">💃 Danse</option>
                    <option value="tennis">🎾 Tennis</option>
                    <option value="football">⚽ Football</option>
                    <option value="basketball">🏀 Basketball</option>
                </select>
            </div>

            <!-- ===== CHAMP 2: Durée en minutes ===== -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Durée (minutes)</label>
                <input type="number"
                       x-model.number="activity.duration"
                       @input="updateCaloriesEstimation"
                       min="1" max="480"
                       placeholder="Ex: 30"
                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-400 focus:ring-2 focus:ring-green-200 transition-all">
            </div>

            <!-- ===== CHAMP 3: Calories (optionnel, estimation auto) ===== -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold text-gray-700">Calories dépensées</label>
                    <button type="button"
                            @click="showCalculator = true"
                            class="text-xs text-green-600 hover:text-green-800 font-medium underline">
                        📊 Calculateur MET
                    </button>
                </div>

                <div class="space-y-3">
                    <!-- Champ principal -->
                    <input type="number"
                           x-model.number="activity.calories"
                           min="1" max="3000"
                           placeholder="Laisser vide pour estimation automatique"
                           class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-400 focus:ring-2 focus:ring-green-200 transition-all"
                           :class="activity.calories && activity.calories < 10 ? 'border-red-300 focus:border-red-400' : ''">

                    <!-- Messages d'aide -->
                    <div class="text-xs space-y-1">
                        <!-- Estimation automatique -->
                        <div x-show="estimatedCalories > 0 && activity.calories == null"
                             class="flex items-center gap-2 text-gray-600">
                            <span class="inline-block w-2 h-2 bg-green-400 rounded-full"></span>
                            <span>Estimation automatique: <strong x-text="estimatedCalories"></strong> calories</span>
                            <button @click="activity.calories = estimatedCalories"
                                    class="text-green-600 hover:text-green-800 underline text-xs">
                                Utiliser
                            </button>
                        </div>

                        <!-- Calories personnalisées -->
                        <div x-show="activity.calories != null && activity.calories >= 10"
                             class="flex items-center gap-2 text-blue-600">
                            <span class="inline-block w-2 h-2 bg-blue-400 rounded-full"></span>
                            <span>Calories personnalisées saisies</span>
                        </div>

                        <!-- Avertissement valeur faible -->
                        <div x-show="activity.calories != null && activity.calories < 10 && activity.calories > 0"
                             class="flex items-center gap-2 text-red-600">
                            <span class="inline-block w-2 h-2 bg-red-400 rounded-full"></span>
                            <span>Valeur inhabituellement basse (min. recommandé: 10 cal)</span>
                        </div>

                        <!-- Rappel des moyennes -->
                        <div x-show="activity.type && activity.duration > 0"
                             class="text-gray-500 border-t pt-2 mt-2">
                            <div class="flex items-center gap-1 mb-1">
                                <span class="text-xs">💡</span>
                                <span class="text-xs font-medium">Moyennes pour cette activité:</span>
                            </div>
                            <div class="text-xs pl-4 space-y-1">
                                <div x-text="getActivityAverages(activity.type)"></div>
                                <div class="text-gray-400" x-text="'Basé sur données médicales MET (personne de ' + (userProfile.weight || 70) + 'kg)'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit"
                    :disabled="loading"
                    class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold hover:from-green-600 hover:to-emerald-700 transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl">
                <span x-show="!loading" class="flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    Ajouter l'activité
                </span>
                <span x-show="loading" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Ajout en cours...
                </span>
            </button>
        </form>
    </div>
</div>
