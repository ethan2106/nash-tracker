<?php

/**
 * Composant: Modal quantité pour ajout repas.
 * @description Modal pour saisir la quantité avant d'ajouter un aliment au repas
 * @requires JavaScript - Écouteurs dans alpine-catalog.js
 */

declare(strict_types=1);
?>
<!-- ========== MODAL D'AJOUT AU REPAS AVEC QUANTITÉ ========== -->
<div id="quantity-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[var(--z-overlay)] hidden flex items-center justify-center p-4">
    <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl max-w-md w-full">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">Ajouter au repas</h3>
            <button id="close-quantity-modal" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6">
            <!-- Sélection du type de repas -->
            <div class="mb-6">
                <label for="meal-type-select" class="block text-sm font-medium text-gray-700 mb-2">
                    Type de repas
                </label>
                <select id="meal-type-select" class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-purple-300 focus:border-transparent shadow-sm">
                    <option value="petit-dejeuner">Petit-déjeuner</option>
                    <option value="dejeuner">Déjeuner</option>
                    <option value="gouter">Goûter</option>
                    <option value="diner">Dîner</option>
                    <option value="en-cas">En-cas</option>
                </select>
            </div>

            <!-- Informations de l'aliment -->
            <div class="mb-6">
                <h4 id="quantity-food-name" class="text-lg font-semibold text-gray-800 mb-2"></h4>
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-sm text-gray-600 mb-2">Valeurs pour 100g :</div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>Calories: <span id="quantity-base-calories" class="font-semibold">0</span> kcal</div>
                        <div>Protéines: <span id="quantity-base-proteins" class="font-semibold">0</span>g</div>
                        <div>Glucides: <span id="quantity-base-carbs" class="font-semibold">0</span>g</div>
                        <div>Sucres: <span id="quantity-base-sugars" class="font-semibold">0</span>g</div>
                        <div>Lipides: <span id="quantity-base-fat" class="font-semibold">0</span>g</div>
                        <div>Graisses sat.: <span id="quantity-base-saturated-fat" class="font-semibold">0</span>g</div>
                    </div>
                </div>
            </div>

            <!-- Saisie de la quantité -->
            <div class="mb-6">
                <label for="quantity-input" class="block text-sm font-medium text-gray-700 mb-2">
                    Quantité (grammes)
                </label>
                <input type="number" id="quantity-input" min="1" max="2000" value="100"
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-purple-300 focus:border-transparent shadow-sm">
            </div>

            <!-- Valeurs calculées -->
            <div class="mb-6">
                <div class="text-sm font-medium text-gray-700 mb-3">Valeurs pour <span id="quantity-display">100</span>g :</div>
                <div class="bg-purple-50 rounded-xl p-4">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between">
                            <span>Calories:</span>
                            <span id="quantity-calc-calories" class="font-bold text-purple-700">0 kcal</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Protéines:</span>
                            <span id="quantity-calc-proteins" class="font-bold text-purple-700">0g</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Glucides:</span>
                            <span id="quantity-calc-carbs" class="font-bold text-purple-700">0g</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Sucres:</span>
                            <span id="quantity-calc-sugars" class="font-bold text-purple-700">0g</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Lipides:</span>
                            <span id="quantity-calc-fat" class="font-bold text-purple-700">0g</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Graisses sat.:</span>
                            <span id="quantity-calc-saturated-fat" class="font-bold text-purple-700">0g</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex gap-3">
                <button id="confirm-add-to-meal" class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all">
                    <i class="fa-solid fa-plus mr-2"></i>Ajouter au repas
                </button>
                <button id="cancel-add-to-meal" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-2xl transition-colors">
                    <i class="fa-solid fa-times mr-2"></i>Annuler
                </button>
            </div>
        </div>
    </div>
</div>
