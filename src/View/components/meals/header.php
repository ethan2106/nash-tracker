<?php

/**
 * Composant: Header Page Repas.
 *
 * @description Titre principal + Sélecteur de date
 * @requires Alpine.js - Variables: selectedDate, isToday
 * @requires Alpine.js - Méthodes: changeDate(), goToToday()
 *
 * @var string $selectedDate Date sélectionnée (Y-m-d)
 */

declare(strict_types=1);

$todayDate = date('Y-m-d');
?>
<!-- ============================================================
     HEADER PAGE REPAS
     - Titre avec icône (style harmonisé)
     - Sélecteur de date avec bouton "Aujourd'hui"
     ============================================================ -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-8 border border-blue-100">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <!-- Titre et description -->
        <div class="flex items-center gap-4">
            <div class="text-purple-500 text-4xl">
                <i class="fa-solid fa-utensils" aria-hidden="true"></i>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Mes Repas</h1>
                <p class="text-gray-600">Composez vos repas et suivez votre alimentation quotidienne</p>
            </div>
        </div>
        
        <!-- Sélecteur de date -->
        <div class="flex items-center gap-4 bg-white/60 rounded-2xl p-4 border border-purple-100">
            <label for="date" class="text-gray-700 font-medium whitespace-nowrap">
                <i class="fa-solid fa-calendar mr-2 text-purple-400" aria-hidden="true"></i>Date :
            </label>
            
            <!-- Input date (Alpine.js x-model) -->
            <input type="date" id="date" name="date" 
                   x-model="selectedDate"
                   @change="changeDate(selectedDate)"
                   class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white"
                   aria-label="Sélectionner la date des repas à afficher">
            
            <!-- Bouton Aujourd'hui -->
            <button type="button" 
                    @click="goToToday()"
                    :class="selectedDate !== '<?= $todayDate; ?>' ? 'bg-purple-500 hover:bg-purple-600' : 'bg-gray-300 cursor-not-allowed'"
                    :disabled="selectedDate === '<?= $todayDate; ?>'"
                    :aria-pressed="selectedDate === '<?= $todayDate; ?>'"
                    aria-label="Voir les repas d'aujourd'hui"
                    class="px-4 py-2 text-white rounded-xl transition-colors font-medium">
                <i class="fa-solid fa-calendar-day mr-1" aria-hidden="true"></i> Aujourd'hui
            </button>
        </div>
    </div>
    
    <!-- Indicateur lecture seule si pas aujourd'hui -->
    <div class="mt-4 text-sm" x-show="!isToday">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 border border-amber-200 rounded-xl text-amber-700">
            <i class="fa-solid fa-eye" aria-hidden="true"></i>
            <span>Mode lecture seule - Les repas passés ne peuvent pas être modifiés</span>
        </div>
    </div>
</div>
