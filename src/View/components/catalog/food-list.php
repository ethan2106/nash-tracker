<?php

/**
 * Composant: Liste des aliments du catalogue.
 * @description Affiche la liste verticale des aliments avec leurs informations nutritionnelles
 * @requires Alpine.js - Variables: paginatedFoods, openDetailsModal(), isFromApi(), parseAutresInfos()
 */

declare(strict_types=1);
?>
<!-- ========== CATALOGUE - LISTE DES ALIMENTS ========== -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/50 p-8">
    <div class="flex flex-col gap-6">
        <template x-for="food in paginatedFoods" :key="food.id">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/50 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-start gap-6">
                    <!-- Image -->
                    <div class="flex-shrink-0 flex items-center justify-center w-20 h-20">
                        <template x-if="food.image_path || (food.autres_infos && parseAutresInfos(food.autres_infos).image_url)">
                            <img :src="food.image_path || parseAutresInfos(food.autres_infos).image_url"
                                 :alt="food.nom"
                                 class="w-20 h-20 object-cover rounded-xl shadow-md">
                        </template>
                        <template x-if="!food.image_path && (!food.autres_infos || !parseAutresInfos(food.autres_infos).image_url)">
                            <div class="w-20 h-20 bg-gray-200 rounded-xl flex items-center justify-center">
                                <i class="fa-solid fa-utensils text-gray-400 text-xl"></i>
                            </div>
                        </template>
                    </div>

                    <!-- Infos -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-xl font-bold text-gray-800" x-text="food.nom"></h3>
                                </div>
                                
                                <!-- Provenance et code-barre -->
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                          :class="isFromApi(food) ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                                        <i class="fa-solid mr-1" :class="isFromApi(food) ? 'fa-globe' : 'fa-edit'"></i>
                                        <span x-text="isFromApi(food) ? 'OpenFoodFacts' : 'Manuel'"></span>
                                    </span>
                                    
                                    <template x-if="isFromApi(food) && food.openfoodfacts_id">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fa-solid fa-barcode mr-1"></i>
                                            Code: <span x-text="food.openfoodfacts_id"></span>
                                        </span>
                                    </template>
                                    <!-- Badge qualité -->
                                    <div class="inline-block" x-html="food.quality_html"></div>
                                </div>

                                <!-- Valeurs nutritionnelles pour 100g -->
                                <div class="bg-gray-50 rounded-xl p-4 mb-3">
                                    <div class="text-sm text-gray-600 mb-2 font-medium">Pour 100g :</div>

                                    <!-- Calories en évidence -->
                                    <template x-if="food.calories_100g !== null && food.calories_100g !== undefined">
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-bold">
                                                <i class="fa-solid fa-fire mr-1"></i>
                                                <span x-text="food.calories_100g || 0"></span> kcal
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Macros principales -->
                                    <div class="grid grid-cols-3 gap-3">
                                        <template x-if="food.proteines_100g !== null && food.proteines_100g !== undefined">
                                            <div class="text-center">
                                                <div class="text-lg font-bold text-purple-600" x-text="food.proteines_100g || 0"></div>
                                                <div class="text-xs text-purple-500 font-medium">Protéines</div>
                                            </div>
                                        </template>
                                        <template x-if="food.glucides_100g !== null && food.glucides_100g !== undefined">
                                            <div class="text-center">
                                                <div class="text-lg font-bold text-blue-600" x-text="food.glucides_100g || 0"></div>
                                                <div class="text-xs text-blue-500 font-medium">Glucides</div>
                                            </div>
                                        </template>
                                        <template x-if="food.lipides_100g !== null && food.lipides_100g !== undefined">
                                            <div class="text-center">
                                                <div class="text-lg font-bold text-orange-600" x-text="food.lipides_100g || 0"></div>
                                                <div class="text-xs text-orange-500 font-medium">Lipides</div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Détails supplémentaires si disponibles -->
                                    <template x-if="food.sucres_100g !== null || food.fibres_100g !== null">
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <div class="flex justify-between text-xs">
                                                <template x-if="food.sucres_100g !== null && food.sucres_100g !== undefined">
                                                    <span class="text-pink-600">
                                                        <i class="fa-solid fa-candy-cane mr-1"></i>
                                                        Sucres: <span x-text="food.sucres_100g || 0"></span>g
                                                    </span>
                                                </template>
                                                <template x-if="food.fibres_100g !== null && food.fibres_100g !== undefined">
                                                    <span class="text-green-600">
                                                        <i class="fa-solid fa-leaf mr-1"></i>
                                                        Fibres: <span x-text="food.fibres_100g || 0"></span>g
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="openDetailsModal(food)"
                                        class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl font-semibold transition-colors">
                                    <i class="fa-solid fa-eye mr-2"></i>Détails
                                </button>
                                <button type="button" class="add-to-meal-from-catalog-btn px-4 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-xl font-semibold transition-colors"
                                        :data-food-id="food.id"
                                        :data-food-name="food.nom"
                                        :data-food-calories="food.calories_100g || 0"
                                        :data-food-proteins="food.proteines_100g || 0"
                                        :data-food-carbs="food.glucides_100g || 0"
                                        :data-food-sugars="food.sucres_100g || 0"
                                        :data-food-fat="food.lipides_100g || 0"
                                        :data-food-saturated-fat="food.acides_gras_satures_100g || 0"
                                        :data-food-fibres="food.fibres_100g || 0">
                                    <i class="fa-solid fa-plus mr-2"></i>Ajouter au repas
                                </button>
                                <form method="POST" action="?page=catalog&action=supprimer-aliment" style="display: inline;" onsubmit="return confirm('Supprimer cet aliment ?')">
                                    <input type="hidden" name="aliment_id" x-bind:value="food.id">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                    <button type="submit" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-xl font-semibold transition-colors">
                                        <i class="fa-solid fa-trash mr-2"></i>Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- État vide -->
        <template x-if="paginatedFoods.length === 0">
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">
                    <i class="fa-solid fa-book"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Votre catalogue est vide</h3>
                <p class="text-gray-500 mb-4">Commencez par rechercher et sauvegarder des aliments</p>
                <a href="?page=food" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <i class="fa-solid fa-plus mr-2"></i>Aller rechercher des aliments
                </a>
            </div>
        </template>
    </div>
</div>
