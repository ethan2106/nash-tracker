<?php

/**
 * Composant: En-tête du catalogue.
 * @description Affiche le titre, barre de recherche et statistiques
 * @requires Alpine.js - Variables: searchQuery, foods, paginatedFoods, setSearchQuery(), clearSearch()
 */

declare(strict_types=1);
?>
<!-- ========== HEADER CATALOG ========== -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-blue-100 mb-8">
    <div class="flex items-center gap-4 mb-6">
        <div class="text-orange-500 text-4xl">
            <i class="fa-solid fa-book" aria-hidden="true"></i>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Catalogue Alimentaire</h1>
            <p class="text-gray-600">Gérez vos aliments sauvegardés et leurs valeurs nutritionnelles</p>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 mb-6">
        <div class="flex gap-4">
            <div class="flex-1">
                <input type="text" x-model="searchQuery"
                       @input="setSearchQuery($event.target.value)"
                       placeholder="Rechercher dans votre catalogue..."
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white/80 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:border-transparent shadow-sm">
            </div>
            <button @click="clearSearch()"
                    class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white font-semibold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <i class="fa-solid fa-times mr-2" aria-hidden="true"></i>Effacer
            </button>
            <button class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 text-white font-semibold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <i class="fa-solid fa-search mr-2" aria-hidden="true"></i>Rechercher
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total aliments</p>
                    <p class="text-3xl font-bold" x-text="foods.length">0</p>
                </div>
                <div class="text-4xl opacity-80">
                    <i class="fa-solid fa-utensils" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Aliments affichés</p>
                    <p class="text-3xl font-bold" x-text="paginatedFoods.length">0</p>
                </div>
                <div class="text-4xl opacity-80">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Recherche active</p>
                    <p class="text-3xl font-bold" x-text="searchQuery ? 'Oui' : 'Non'">Non</p>
                </div>
                <div class="text-4xl opacity-80">
                    <i class="fa-solid fa-search" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
</div>
