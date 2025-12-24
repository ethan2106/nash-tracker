<?php

/**
 * Composant: Modal détails nutritionnels complets.
 * @description Modal affichant toutes les informations nutritionnelles d'un produit
 * @requires Alpine.js - Variables: showDetailsModal, selectedFood, closeDetailsModal()
 */

declare(strict_types=1);
?>
<!-- ========== MODAL DÉTAILS COMPLETS ========== -->
<div x-show="showDetailsModal" 
     x-cloak
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[var(--z-overlay)] flex items-center justify-center p-4"
     @click.self="closeDetailsModal()">
    <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
         x-show="showDetailsModal && selectedFood"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-purple-600">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-utensils text-white text-2xl"></i>
                <h3 class="text-2xl font-bold text-white">Détails nutritionnels complets</h3>
            </div>
            <button @click="closeDetailsModal()" class="text-white/80 hover:text-white p-2 rounded-xl hover:bg-white/20 transition-colors">
                <i class="fa-solid fa-times text-2xl"></i>
            </button>
        </div>

        <div class="p-6">
            <!-- En-tête du produit -->
            <div class="flex items-start gap-6 mb-6">
                <!-- Image -->
                <div class="flex-shrink-0">
                    <template x-if="selectedFood?.image">
                        <img :src="selectedFood.image" :alt="selectedFood?.name" class="w-32 h-32 object-cover rounded-2xl shadow-lg border-4 border-white">
                    </template>
                    <template x-if="!selectedFood?.image">
                        <div class="w-32 h-32 bg-gray-200 rounded-2xl flex items-center justify-center shadow-lg border-4 border-white">
                            <i class="fa-solid fa-utensils text-gray-400 text-3xl"></i>
                        </div>
                    </template>
                </div>
                
                <!-- Infos principales -->
                <div class="flex-1">
                    <h4 class="text-2xl font-bold text-gray-800 mb-2" x-text="selectedFood?.name"></h4>
                    <template x-if="selectedFood?.brands">
                        <p class="text-lg text-gray-600 mb-2">Marque: <span x-text="selectedFood.brands"></span></p>
                    </template>
                    <template x-if="selectedFood?.barcode || selectedFood?.code">
                        <p class="text-sm text-gray-500 mb-4">
                            <i class="fa-solid fa-barcode mr-1"></i>
                            Code-barre: <span x-text="selectedFood?.barcode || selectedFood?.code" class="font-mono"></span>
                        </p>
                    </template>
                    
                    <!-- Badge qualité -->
                    <div class="mb-4" x-html="selectedFood?.qualityBadgeHtml || ''"></div>
                </div>
            </div>

            <!-- Valeurs nutritionnelles principales -->
            <div class="mb-6">
                <h5 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fa-solid fa-chart-pie mr-2 text-blue-500"></i>
                    Valeurs nutritionnelles (pour 100g)
                </h5>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Calories -->
                    <div class="bg-yellow-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-700" x-text="formatNutrient(selectedFood, 'energy-kcal', 'kcal')"></div>
                        <div class="text-sm text-yellow-600">Calories</div>
                    </div>
                    <!-- Protéines -->
                    <div class="bg-purple-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-purple-700" x-text="formatNutrient(selectedFood, 'proteins', 'g')"></div>
                        <div class="text-sm text-purple-600">Protéines</div>
                    </div>
                    <!-- Glucides -->
                    <div class="bg-blue-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-blue-700" x-text="formatNutrient(selectedFood, 'carbohydrates', 'g')"></div>
                        <div class="text-sm text-blue-600">Glucides</div>
                    </div>
                    <!-- Sucres -->
                    <div class="bg-pink-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-pink-700" x-text="formatNutrient(selectedFood, 'sugars', 'g')"></div>
                        <div class="text-sm text-pink-600">Sucres</div>
                    </div>
                    <!-- Lipides -->
                    <div class="bg-orange-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-orange-700" x-text="formatNutrient(selectedFood, 'fat', 'g')"></div>
                        <div class="text-sm text-orange-600">Lipides</div>
                    </div>
                    <!-- Graisses saturées -->
                    <div class="bg-red-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-red-700" x-text="formatNutrient(selectedFood, 'saturated-fat', 'g')"></div>
                        <div class="text-sm text-red-600">Graisses sat.</div>
                    </div>
                    <!-- Fibres -->
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-green-700" x-text="formatNutrient(selectedFood, 'fiber', 'g')"></div>
                        <div class="text-sm text-green-600">Fibres</div>
                    </div>
                    <!-- Sel -->
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-gray-700" x-text="formatNutrient(selectedFood, 'salt', 'g')"></div>
                        <div class="text-sm text-gray-600">Sel</div>
                    </div>
                </div>
            </div>

            <!-- Analyse qualité nutritionnelle -->
            <div class="mb-6" x-show="selectedFood?.nutriments">
                <h5 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fa-solid fa-clipboard-check mr-2 text-green-500"></i>
                    Analyse nutritionnelle
                </h5>
                <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6">
                    <div class="grid md:grid-cols-2 gap-4">
                        <!-- Protéines -->
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-700">Protéines</span>
                                <span class="text-sm font-bold" x-text="formatNutrient(selectedFood, 'proteins', 'g/100g')"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" :style="'width: ' + Math.min(100, (getNutrientValue(selectedFood, 'proteins') / 20) * 100) + '%'"></div>
                            </div>
                            <p class="text-xs mt-1" :class="getNutrientValue(selectedFood, 'proteins') >= 15 ? 'text-green-600' : 'text-orange-600'"
                               x-text="getNutrientValue(selectedFood, 'proteins') >= 15 ? '✓ Conforme (≥15g)' : '⚠ Attention (<15g)'"></p>
                        </div>
                        <!-- Fibres -->
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-700">Fibres</span>
                                <span class="text-sm font-bold" x-text="formatNutrient(selectedFood, 'fiber', 'g/100g')"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" :style="'width: ' + Math.min(100, (getNutrientValue(selectedFood, 'fiber') / 6) * 100) + '%'"></div>
                            </div>
                            <p class="text-xs mt-1" :class="getNutrientValue(selectedFood, 'fiber') >= 3 ? 'text-green-600' : 'text-orange-600'"
                               x-text="getNutrientValue(selectedFood, 'fiber') >= 3 ? '✓ Conforme (≥3g)' : '⚠ Attention (<3g)'"></p>
                        </div>
                        <!-- Graisses saturées -->
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-700">Graisses saturées</span>
                                <span class="text-sm font-bold" x-text="formatNutrient(selectedFood, 'saturated-fat', 'g/100g')"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" :style="'width: ' + Math.min(100, (getNutrientValue(selectedFood, 'saturated-fat') / 5) * 100) + '%'"></div>
                            </div>
                            <p class="text-xs mt-1" :class="getNutrientValue(selectedFood, 'saturated-fat') <= 3 ? 'text-green-600' : 'text-orange-600'"
                               x-text="getNutrientValue(selectedFood, 'saturated-fat') <= 3 ? '✓ Conforme (≤3g)' : '⚠ Attention (>3g)'"></p>
                        </div>
                        <!-- Sucres -->
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-700">Sucres</span>
                                <span class="text-sm font-bold" x-text="formatNutrient(selectedFood, 'sugars', 'g/100g')"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-pink-500 h-2 rounded-full" :style="'width: ' + Math.min(100, (getNutrientValue(selectedFood, 'sugars') / 10) * 100) + '%'"></div>
                            </div>
                            <p class="text-xs mt-1" :class="getNutrientValue(selectedFood, 'sugars') <= 5 ? 'text-green-600' : 'text-orange-600'"
                               x-text="getNutrientValue(selectedFood, 'sugars') <= 5 ? '✓ Conforme (≤5g)' : '⚠ Attention (>5g)'"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="grid md:grid-cols-2 gap-6 mb-6" x-show="selectedFood?.ingredients || selectedFood?.categories">
                <template x-if="selectedFood?.ingredients">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fa-solid fa-list mr-2 text-orange-500"></i>
                            Ingrédients
                        </h5>
                        <p class="text-gray-600 text-sm leading-relaxed bg-orange-50 rounded-xl p-4" x-text="selectedFood.ingredients"></p>
                    </div>
                </template>
                <template x-if="selectedFood?.categories">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fa-solid fa-folder mr-2 text-blue-500"></i>
                            Catégories
                        </h5>
                        <p class="text-gray-600 text-sm bg-blue-50 rounded-xl p-4" x-text="selectedFood.categories"></p>
                    </div>
                </template>
            </div>

            <!-- Note explicative -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <p class="text-xs text-gray-500 flex items-start gap-2">
                    <i class="fa-solid fa-info-circle mt-0.5"></i>
                    <span>Ce score est un indicateur éducatif basé sur des critères nutritionnels généraux et simplifiés. Il ne remplace pas un avis médical ou diététique professionnel.</span>
                </p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button @click="saveFood(selectedFood)" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <i class="fa-solid fa-save mr-2"></i>Sauvegarder en base
                </button>
                <button @click="openQuantityModal(selectedFood)" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300"
                        x-show="mealType !== 'repas'">
                    <i class="fa-solid fa-plus mr-2"></i>Ajouter au repas
                </button>
                <button @click="closeDetailsModal()" 
                        class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-2xl transition-colors">
                    <i class="fa-solid fa-times mr-2"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>
