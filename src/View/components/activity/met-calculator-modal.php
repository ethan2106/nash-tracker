<?php

/**
 * Composant: Modal Calculateur MET Avancé.
 *
 * @description Calculateur de calories basé sur le système MET médical
 * @formula Calories = MET × Poids(kg) × Durée(heures)
 *
 * @requires Alpine.js - Variables: showCalculator, calculator, userProfile
 * @requires Alpine.js - Méthodes: calculateAdvancedCalories(), applyCalculatedCalories()
 *
 * @see https://en.wikipedia.org/wiki/Metabolic_equivalent_of_task
 */

declare(strict_types=1);
?>
<!-- ============================================================
     MODAL CALCULATEUR MET
     - Position fixed, z-50 (au-dessus de tout)
     - Fermeture: clic extérieur ou bouton X
     ============================================================ -->
<div x-show="showCalculator"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[var(--z-overlay)] p-4"
     @click.self="showCalculator = false">

    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">📊 Calculateur MET Avancé</h3>
                <button @click="showCalculator = false"
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Informations pré-remplies depuis le profil -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-blue-600">ℹ️</span>
                        <span class="text-sm font-medium text-blue-800">Données du profil</span>
                    </div>
                    <div class="text-sm text-blue-700 space-y-1">
                        <div>Poids: <span x-text="userProfile.weight || 'Non défini'"></span> kg</div>
                        <div>Âge: <span x-text="userProfile.age || 'Non défini'"></span> ans</div>
                        <div>Sexe: <span x-text="userProfile.gender || 'Non défini'"></span></div>
                    </div>
                </div>

                <!-- ===== FORMULAIRE DE CALCUL MET ===== 
                     Champs: activité, durée, intensité, poids
                     Résultat affiché en temps réel -->
                <form @submit.prevent="calculateAdvancedCalories" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Activité</label>
                        <select x-model="calculator.activity"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-400 focus:ring-2 focus:ring-green-200">
                            <option value="">-- Choisir --</option>
                            <option value="marche">🚶 Marche</option>
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

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Durée (minutes)</label>
                        <input type="number"
                               x-model.number="calculator.duration"
                               min="1" max="480"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-400 focus:ring-2 focus:ring-green-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Intensité</label>
                        <select x-model="calculator.intensity"
                                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-400 focus:ring-2 focus:ring-green-200">
                            <option value="light">Légère (débutant)</option>
                            <option value="moderate">Modérée (standard)</option>
                            <option value="vigorous">Intense (avancé)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Poids (kg)
                            <span x-show="userProfile.weight" class="text-xs text-green-600 font-normal">(auto-rempli depuis IMC)</span>
                        </label>
                        <input type="number"
                               x-model.number="calculator.weight"
                               :value="userProfile.weight || 70"
                               min="30" max="200"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-400 focus:ring-2 focus:ring-green-200">
                        <p class="text-xs text-gray-500 mt-1">
                            <span x-show="!userProfile.weight">Définissez votre poids dans la section IMC pour un auto-remplissage</span>
                            <span x-show="userProfile.weight">Modifiable si nécessaire</span>
                        </p>
                    </div>

                    <div x-show="calculator.result > 0" class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" x-text="calculator.result"></div>
                            <div class="text-sm text-green-700">calories estimées</div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 px-4 py-3 bg-green-500 text-white rounded-xl font-semibold hover:bg-green-600 transition-colors">
                            📊 Calculer
                        </button>
                        <button type="button"
                                @click="applyCalculatedCalories"
                                x-show="calculator.result > 0"
                                class="flex-1 px-4 py-3 bg-blue-500 text-white rounded-xl font-semibold hover:bg-blue-600 transition-colors">
                            ✓ Utiliser ce résultat
                        </button>
                    </div>
                </form>

                <!-- Informations éducatives -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-gray-600">🎓</span>
                        <span class="text-sm font-medium text-gray-800">À propos du calcul MET</span>
                    </div>
                    <div class="text-xs text-gray-600 space-y-1">
                        <p>• MET = Metabolic Equivalent of Task</p>
                        <p>• Basé sur des données médicales validées</p>
                        <p>• Adapté au poids et à l'intensité</p>
                        <p>• Plus précis que les moyennes générales</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
