<?php

/**
 * Composant: En-tête et formulaires de recherche.
 * @description Titre, onglets et formulaires de recherche (texte, code-barre, manuel)
 * @requires Alpine.js - Variables: activeTab, searchQuery, barcodeQuery, manualForm
 * @var string $mealType Type de repas actuel
 * @var string $currentMealLabel Label du repas affiché
 */

declare(strict_types=1);
?>
<!-- ========== HEADER RECHERCHE ========== -->
<div class="bg-gradient-to-r from-white/80 via-blue-50/80 to-purple-50/80 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-8 border border-white/50 z-10">
    <div class="flex items-center gap-4 mb-6">
        <div class="text-green-500 text-4xl">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Ajouter un Aliment</h1>
            <p class="text-gray-600">Recherchez et ajoutez de nouveaux aliments à votre catalogue personnel</p>
            <?php if ($mealType !== 'repas')
            { ?>
            <p class="text-sm text-blue-600 mt-1">
                <i class="fa-solid fa-utensils mr-1"></i>Ajouter au <?= htmlspecialchars($currentMealLabel); ?>
            </p>
            <?php } else
            { ?>
            <p class="text-sm text-green-600 mt-1">
                <i class="fa-solid fa-bolt mr-1"></i>Recherches optimisées avec cache automatique
            </p>
            <?php } ?>
        </div>
    </div>

    <!-- Formulaire de recherche -->
    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 mb-6">
        <!-- Onglets de recherche -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-6" role="tablist">
            <button type="button" @click="switchTab('text')" 
                    :class="activeTab === 'text' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'" 
                    class="tab-button px-3 py-2 rounded-xl font-semibold transition-all text-sm">
                <i class="fa-solid fa-magnifying-glass mr-1"></i>Rechercher
            </button>
            <button type="button" @click="switchTab('barcode')" 
                    :class="activeTab === 'barcode' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'" 
                    class="tab-button px-3 py-2 rounded-xl font-semibold transition-all text-sm">
                <i class="fa-solid fa-barcode mr-1"></i>Scanner
            </button>
            <button type="button" @click="switchTab('manual')" 
                    :class="activeTab === 'manual' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600'" 
                    class="tab-button px-3 py-2 rounded-xl font-semibold transition-all text-sm">
                <i class="fa-solid fa-plus-circle mr-1"></i>Créer
            </button>
        </div>

        <!-- Formulaire recherche texte -->
        <form method="POST" action="?page=food<?= $mealType !== 'repas' ? '&meal_type=' . urlencode($mealType) : ''; ?>" 
              x-show="activeTab === 'text'" x-transition>
            <input type="hidden" name="page" value="food">
            <input type="hidden" name="search_type" value="text">
            <input type="hidden" name="meal_type" value="<?= htmlspecialchars($mealType); ?>">
            <div class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="search_query" x-model="searchQuery"
                           placeholder="Rechercher un aliment à ajouter au catalogue (ex: banane, poulet, riz, coca cola)..."
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-green-300 focus:border-transparent shadow-sm"
                           required>
                </div>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-sky-500 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <i class="fa-solid fa-search mr-2"></i>Rechercher
                </button>
            </div>
        </form>

        <!-- Formulaire recherche code-barre -->
        <form method="POST" action="?page=food<?= $mealType !== 'repas' ? '&meal_type=' . urlencode($mealType) : ''; ?>" 
              x-show="activeTab === 'barcode'" x-transition>
            <input type="hidden" name="page" value="food">
            <input type="hidden" name="search_type" value="barcode">
            <input type="hidden" name="meal_type" value="<?= htmlspecialchars($mealType); ?>">
            <div class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="barcode" x-model="barcodeQuery"
                           placeholder="Scanner un code-barre pour ajouter l'aliment au catalogue (ex: 5449000000996)..."
                           pattern="[0-9]{8,18}"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent shadow-sm"
                           required>
                </div>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-sky-500 to-purple-500 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <i class="fa-solid fa-barcode mr-2"></i>Scanner
                </button>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fa-solid fa-info-circle mr-1"></i>
                Scannez le code-barre d'un produit pour l'ajouter automatiquement à votre catalogue
            </p>
        </form>

        <!-- Formulaire ajout manuel -->
        <form method="POST" action="?page=food<?= $mealType !== 'repas' ? '&meal_type=' . urlencode($mealType) : ''; ?>" 
              x-show="activeTab === 'manual'" x-transition enctype="multipart/form-data">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Créer un aliment personnalisé</h3>
                <p class="text-sm text-gray-600">Ajoutez manuellement un aliment à votre catalogue avec ses valeurs nutritionnelles</p>
            </div>
            <input type="hidden" name="page" value="food">
            <input type="hidden" name="add_manual" value="1">
            <input type="hidden" name="meal_type" value="<?= htmlspecialchars($mealType); ?>">

            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="manual_name" class="block text-sm font-medium text-gray-700 mb-2">Nom de l'aliment *</label>
                    <input type="text" id="manual_name" x-model="manualForm.name" name="manual_name" required
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
                </div>
                <div>
                    <label for="manual_brand" class="block text-sm font-medium text-gray-700 mb-2">Marque</label>
                    <input type="text" id="manual_brand" x-model="manualForm.brand" name="manual_brand"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
                </div>
            </div>

            <div class="mb-4">
                <label for="manual_image" class="block text-sm font-medium text-gray-700 mb-2">Image de l'aliment</label>
                <input type="file" id="manual_image" name="manual_image" accept="image/*"
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF. Taille max : 2MB</p>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="manual_calories" class="block text-sm font-medium text-gray-700 mb-2">Calories (kcal/100g) *</label>
                    <input type="number" id="manual_calories" x-model="manualForm.calories" name="manual_calories" step="0.1" required
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
                </div>
                <div>
                    <label for="manual_proteins" class="block text-sm font-medium text-gray-700 mb-2">Protéines (g/100g)</label>
                    <input type="number" id="manual_proteins" x-model="manualForm.proteins" name="manual_proteins" step="0.1"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
                </div>
                <div>
                    <label for="manual_carbs" class="block text-sm font-medium text-gray-700 mb-2">Glucides (g/100g)</label>
                    <input type="number" id="manual_carbs" x-model="manualForm.carbs" name="manual_carbs" step="0.1"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="manual_fat" class="block text-sm font-medium text-gray-700 mb-2">Lipides (g/100g)</label>
                    <input type="number" id="manual_fat" x-model="manualForm.fat" name="manual_fat" step="0.1"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
                </div>
                <div>
                    <label for="manual_fiber" class="block text-sm font-medium text-gray-700 mb-2">Fibres (g/100g)</label>
                    <input type="number" id="manual_fiber" x-model="manualForm.fiber" name="manual_fiber" step="0.1"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
                </div>
                <div>
                    <label for="manual_quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantité (g)</label>
                    <input type="number" id="manual_quantity" x-model="manualForm.quantity" name="manual_quantity" min="1"
                           class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" @click="submitManualForm()" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <i class="fa-solid fa-check mr-2"></i>Créer l'aliment
                </button>
                <button type="button" @click="resetManualForm()" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-2xl transition-colors">
                    <i class="fa-solid fa-undo mr-2"></i>Réinitialiser
                </button>
            </div>
        </form>
    </div>

    <!-- Suggestions de recherche -->
    <div class="bg-white/40 backdrop-blur-sm rounded-2xl p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fa-solid fa-lightbulb mr-2"></i>Suggestions pour enrichir votre catalogue
        </h3>
        <div class="flex flex-wrap gap-2">
            <?php
            $suggestions = ['banane', 'pomme', 'riz', 'poulet', 'lait', 'pain', 'œuf', 'yaourt', 'fromage', 'salade'];
foreach ($suggestions as $suggestion)
{ ?>
            <button type="button" @click="useSuggestion('<?= $suggestion; ?>')" 
                    class="suggestion-btn px-3 py-1 bg-white/60 hover:bg-white/80 text-gray-600 hover:text-gray-800 rounded-full text-sm transition-all">
                <?= $suggestion; ?>
            </button>
            <?php } ?>
        </div>
    </div>
</div>
