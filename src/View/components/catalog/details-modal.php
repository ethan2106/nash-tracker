<?php

/**
 * Composant: Modal détails aliment.
 * @description Modal affichant les informations nutritionnelles complètes
 * @requires Alpine.js - Variables: showDetailsModal, selectedFood, closeDetailsModal(), isFromApi(), parseAutresInfos()
 */

declare(strict_types=1);
?>
<!-- ========== MODAL DÉTAILS ALIMENT ========== -->
<div x-show="showDetailsModal" x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[var(--z-overlay)] flex items-center justify-center p-4"
     @click.self="closeDetailsModal()">
    <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-8" x-show="showDetailsModal && selectedFood">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="text-blue-500 text-3xl">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Détails nutritionnels</h2>
                        <p class="text-gray-600">Informations complètes sur l'aliment</p>
                    </div>
                </div>
                <button @click="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition-colors">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>

            <div class="space-y-8">
                <!-- Informations générales -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6">
                    <div class="flex items-start gap-6">
                        <!-- Image -->
                        <div class="flex-shrink-0">
                            <template x-if="selectedFood.image_path || (selectedFood.autres_infos && parseAutresInfos(selectedFood.autres_infos).image_url)">
                                <img :src="selectedFood.image_path || parseAutresInfos(selectedFood.autres_infos).image_url"
                                     :alt="selectedFood.nom"
                                     class="w-24 h-24 object-cover rounded-2xl shadow-lg border-4 border-white">
                            </template>
                            <template x-if="!selectedFood.image_path && (!selectedFood.autres_infos || !parseAutresInfos(selectedFood.autres_infos).image_url)">
                                <div class="w-24 h-24 bg-gray-200 rounded-2xl flex items-center justify-center shadow-lg border-4 border-white">
                                    <i class="fa-solid fa-utensils text-gray-400 text-3xl"></i>
                                </div>
                            </template>
                        </div>

                        <!-- Détails -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-800 mb-3" x-text="selectedFood.nom"></h3>

                            <!-- Badges -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium"
                                      :class="isFromApi(selectedFood) ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                                    <i class="fa-solid mr-1" :class="isFromApi(selectedFood) ? 'fa-globe' : 'fa-edit'"></i>
                                    <span x-text="isFromApi(selectedFood) ? 'OpenFoodFacts' : 'Ajout manuel'"></span>
                                </span>

                                <template x-if="selectedFood.openfoodfacts_id">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                        <i class="fa-solid fa-barcode mr-1"></i>
                                        Code: <span x-text="selectedFood.openfoodfacts_id"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Informations supplémentaires -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <template x-if="parseAutresInfos(selectedFood.autres_infos).marque">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-tag text-purple-500 w-4"></i>
                                        <span class="text-gray-600">Marque:</span>
                                        <span class="font-medium text-gray-800" x-text="parseAutresInfos(selectedFood.autres_infos).marque"></span>
                                    </div>
                                </template>

                                <template x-if="parseAutresInfos(selectedFood.autres_infos).categorie">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-folder text-blue-500 w-4"></i>
                                        <span class="text-gray-600">Catégorie:</span>
                                        <span class="font-medium text-gray-800" x-text="parseAutresInfos(selectedFood.autres_infos).categorie"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Valeurs nutritionnelles principales -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6">
                    <h4 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fa-solid fa-chart-pie mr-3 text-green-600"></i>
                        Valeurs nutritionnelles (pour 100g)
                    </h4>

                    <!-- Calories en évidence -->
                    <template x-if="selectedFood.calories_100g !== null && selectedFood.calories_100g !== undefined">
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center gap-3 bg-yellow-100 text-yellow-800 px-6 py-3 rounded-2xl shadow-lg">
                                <i class="fa-solid fa-fire text-2xl"></i>
                                <div>
                                    <div class="text-3xl font-bold" x-text="selectedFood.calories_100g"></div>
                                    <div class="text-sm font-medium">kilocalories</div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Macros principales -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <template x-if="selectedFood.proteines_100g !== null && selectedFood.proteines_100g !== undefined">
                            <div class="bg-white rounded-xl p-4 text-center shadow-sm border border-purple-100">
                                <div class="text-2xl font-bold text-purple-600 mb-1" x-text="selectedFood.proteines_100g"></div>
                                <div class="text-purple-500 font-medium">Protéines</div>
                                <div class="text-purple-400 text-sm">g</div>
                            </div>
                        </template>

                        <template x-if="selectedFood.glucides_100g !== null && selectedFood.glucides_100g !== undefined">
                            <div class="bg-white rounded-xl p-4 text-center shadow-sm border border-blue-100">
                                <div class="text-2xl font-bold text-blue-600 mb-1" x-text="selectedFood.glucides_100g"></div>
                                <div class="text-blue-500 font-medium">Glucides</div>
                                <div class="text-blue-400 text-sm">g</div>
                            </div>
                        </template>

                        <template x-if="selectedFood.lipides_100g !== null && selectedFood.lipides_100g !== undefined">
                            <div class="bg-white rounded-xl p-4 text-center shadow-sm border border-orange-100">
                                <div class="text-2xl font-bold text-orange-600 mb-1" x-text="selectedFood.lipides_100g"></div>
                                <div class="text-orange-500 font-medium">Lipides</div>
                                <div class="text-orange-400 text-sm">g</div>
                            </div>
                        </template>
                    </div>

                    <!-- Détails nutritionnels -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <template x-if="selectedFood.sucres_100g !== null && selectedFood.sucres_100g !== undefined">
                            <div class="bg-pink-50 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-pink-600" x-text="selectedFood.sucres_100g"></div>
                                <div class="text-pink-500 text-sm">Sucres</div>
                                <div class="text-pink-400 text-xs">g</div>
                            </div>
                        </template>

                        <template x-if="selectedFood.fibres_100g !== null && selectedFood.fibres_100g !== undefined">
                            <div class="bg-green-50 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-green-600" x-text="selectedFood.fibres_100g"></div>
                                <div class="text-green-500 text-sm">Fibres</div>
                                <div class="text-green-400 text-xs">g</div>
                            </div>
                        </template>

                        <template x-if="selectedFood.acides_gras_satures_100g !== null && selectedFood.acides_gras_satures_100g !== undefined">
                            <div class="bg-red-50 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-red-600" x-text="selectedFood.acides_gras_satures_100g"></div>
                                <div class="text-red-500 text-sm">AGS</div>
                                <div class="text-red-400 text-xs">g</div>
                            </div>
                        </template>

                        <template x-if="selectedFood.sodium_100g !== null && selectedFood.sodium_100g !== undefined">
                            <div class="bg-indigo-50 rounded-lg p-3 text-center">
                                <div class="text-lg font-bold text-indigo-600" x-text="selectedFood.sodium_100g"></div>
                                <div class="text-indigo-500 text-sm">Sodium</div>
                                <div class="text-indigo-400 text-xs">mg</div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Informations complémentaires -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6">
                    <h4 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fa-solid fa-info-circle mr-3 text-purple-600"></i>
                        Informations complémentaires
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Source et date -->
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fa-solid fa-database mr-2 text-blue-500"></i>
                                Source des données
                            </h5>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Origine:</span>
                                    <span class="font-medium" x-text="isFromApi(selectedFood) ? 'OpenFoodFacts API' : 'Saisie manuelle'"></span>
                                </div>
                                <template x-if="selectedFood.openfoodfacts_id">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Code-barres:</span>
                                        <span class="font-medium font-mono text-xs" x-text="selectedFood.openfoodfacts_id"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Conseils nutritionnels -->
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fa-solid fa-lightbulb mr-2 text-yellow-500"></i>
                                Conseils
                            </h5>
                            <div class="text-sm text-gray-600">
                                <template x-if="selectedFood.proteines_100g > 20">
                                    <p class="mb-2">• Excellente source de protéines</p>
                                </template>
                                <template x-if="selectedFood.fibres_100g > 6">
                                    <p class="mb-2">• Riche en fibres</p>
                                </template>
                                <template x-if="selectedFood.calories_100g < 50">
                                    <p class="mb-2">• Aliment peu calorique</p>
                                </template>
                                <template x-if="!selectedFood.proteines_100g && !selectedFood.fibres_100g && selectedFood.calories_100g">
                                    <p class="text-gray-500">• Aliment standard</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button type="button" class="add-to-meal-from-catalog-btn flex-1 bg-gradient-to-r from-green-500 to-blue-500 text-white font-semibold py-3 px-6 rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300"
                            :data-food-id="selectedFood.id"
                            :data-food-name="selectedFood.nom"
                            :data-food-calories="selectedFood.calories_100g || 0"
                            :data-food-proteins="selectedFood.proteines_100g || 0"
                            :data-food-carbs="selectedFood.glucides_100g || 0"
                            :data-food-sugars="selectedFood.sucres_100g || 0"
                            :data-food-fat="selectedFood.lipides_100g || 0"
                            :data-food-saturated-fat="selectedFood.acides_gras_satures_100g || 0"
                            :data-food-fibres="selectedFood.fibres_100g || 0">
                        <i class="fa-solid fa-plus mr-2"></i>Ajouter à un repas
                    </button>

                    <button @click="closeDetailsModal()" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-2xl transition-colors">
                        <i class="fa-solid fa-times mr-2"></i>Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
