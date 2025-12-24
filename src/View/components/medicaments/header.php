<?php

/**
 * Composant: En-tête et sélecteur de date.
 * @description Affiche le titre et le sélecteur de date pour les médicaments
 * @requires Alpine.js - Variables: currentDate, changeDate(), formatDate()
 */

declare(strict_types=1);
?>
<!-- ========== HEADER MÉDICAMENTS ========== -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-8 border border-blue-100">
    <div class="flex items-center gap-4 mb-6">
        <div class="text-purple-500 text-4xl">
            <i class="fa-solid fa-pills" aria-hidden="true"></i>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Gestion des Médicaments</h1>
            <p class="text-gray-600">Suivez vos prises quotidiennes de médicaments</p>
        </div>
    </div>
    
    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" role="region" aria-label="Statistiques des médicaments">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Médicaments réguliers</p>
                    <p class="text-3xl font-bold" x-text="medicamentsSections[0]?.medicaments?.length || 0" aria-live="polite">0</p>
                </div>
                <div class="text-4xl opacity-80">
                    <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Médicaments ponctuels</p>
                    <p class="text-3xl font-bold" x-text="medicamentsSections[1]?.medicaments?.length || 0" aria-live="polite">0</p>
                </div>
                <div class="text-4xl opacity-80">
                    <i class="fa-solid fa-clock" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Prises aujourd'hui</p>
                    <p class="text-3xl font-bold" x-text="getTotalPrisesToday()" aria-live="polite">0</p>
                </div>
                <div class="text-4xl opacity-80">
                    <i class="fa-solid fa-check-double" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== SÉLECTEUR DE DATE ========== -->
<nav class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-6 mb-6 border border-white/50" aria-label="Navigation par date">
    <div class="flex items-center justify-between">
        <button @click="changeDate(-1)" 
                class="p-3 rounded-xl bg-blue-100 hover:bg-blue-200 text-blue-700 transition-colors"
                aria-label="Jour précédent">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <div class="text-center">
            <h2 class="text-xl font-bold text-gray-800" x-text="formatDate(currentDate)" aria-live="polite"></h2>
            <p class="text-sm text-gray-500">Sélectionnez une date pour voir vos prises</p>
        </div>
        <button @click="changeDate(1)" 
                class="p-3 rounded-xl bg-blue-100 hover:bg-blue-200 text-blue-700 transition-colors"
                aria-label="Jour suivant">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>
</nav>
