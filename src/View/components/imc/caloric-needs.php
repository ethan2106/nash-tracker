<?php

/**
 * Composant: Besoins caloriques.
 *
 * @description Affichage BMR, TDEE et objectifs caloriques (perte/maintien/masse)
 * @requires Alpine.js - Variables: bmr, tdee, caloriesPerte, caloriesMaintien, caloriesMasse
 */

declare(strict_types=1);
?>
<!-- ============================================================
     SECTION BESOINS CALORIQUES
     - BMR (Métabolisme de base)
     - TDEE (Dépense énergétique totale) avec progression SVG
     - 3 objectifs : Perte / Maintien / Prise de masse
     ============================================================ -->
<div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-blue-100 p-8 flex flex-col gap-6 mt-12"
     x-show="true"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0 transform translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0">
    
    <!-- ===== EN-TÊTE ===== -->
    <div class="flex flex-col items-center mb-6">
        <i class="fa-solid fa-fire text-yellow-400 text-5xl mb-2 drop-shadow"></i>
        <h2 class="text-3xl font-extrabold text-gray-800 mb-2">Besoins Caloriques</h2>
        <p class="text-gray-500 text-center">Calculés selon vos données</p>
    </div>
    
    <!-- ===== BMR & TDEE ===== -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Card BMR -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 backdrop-blur-sm rounded-2xl shadow-lg p-6 flex flex-col items-center border border-blue-200 hover:shadow-xl transition-all duration-300 group"
             x-show="true"
             x-transition:enter="transition ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            <i class="fa-solid fa-heart-pulse text-blue-500 text-3xl mb-3 group-hover:scale-110 transition-transform"></i>
            <span class="font-semibold text-gray-700 mb-2">Métabolisme de base (BMR)</span>
            <span class="text-blue-600 font-bold text-3xl" id="bmr-value" x-text="Math.round(bmr) + ' kcal/jour'"
                  role="status" aria-label="Métabolisme de base" aria-live="polite"></span>
            <p class="text-xs text-gray-500 mt-2 text-center">Calories brûlées au repos</p>
        </div>
        
        <!-- Card TDEE avec progression circulaire SVG -->
        <div class="bg-gradient-to-br from-green-50 to-emerald-100 backdrop-blur-sm rounded-2xl shadow-lg p-6 flex flex-col items-center border border-green-200 hover:shadow-xl transition-all duration-300 group relative"
             x-show="true"
             x-transition:enter="transition ease-out duration-500 delay-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            <!-- Progression circulaire SVG -->
            <div class="relative w-24 h-24 mb-3">
                <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none" stroke="#d1fae5" stroke-width="3"/>
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none" stroke="#10b981" stroke-width="3"
                          :stroke-dasharray="`${(tdee/3000)*100} 100`"
                          class="transition-all duration-1000"/>
                </svg>
                <i class="fa-solid fa-fire-flame-curved text-green-500 text-2xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 group-hover:scale-110 transition-transform"></i>
            </div>
            <span class="font-semibold text-gray-700 mb-2">Dépense totale (TDEE)</span>
            <span class="text-green-600 font-bold text-3xl" id="tdee-value" x-text="Math.round(tdee) + ' kcal/jour'"
                  role="status" aria-label="Dépense énergétique totale" aria-live="polite"></span>
            <p class="text-xs text-gray-500 mt-2 text-center">Calories brûlées par jour</p>
        </div>
    </div>
    
    <!-- ===== OBJECTIFS CALORIQUES ===== -->
    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Perte de poids -->
        <div class="bg-white/80 rounded-xl shadow p-4 flex flex-col items-center">
            <span class="font-semibold text-gray-700">Perte de poids</span>
            <span class="text-red-600 font-bold text-lg" id="perte-value" x-text="caloriesPerte + ' kcal/jour'"
                  role="status" aria-label="Calories pour perte de poids" aria-live="polite"></span>
        </div>
        
        <!-- Maintien -->
        <div class="bg-white/80 rounded-xl shadow p-4 flex flex-col items-center">
            <span class="font-semibold text-gray-700">Maintien</span>
            <span class="text-yellow-600 font-bold text-lg" id="maintien-value" x-text="caloriesMaintien + ' kcal/jour'"
                  role="status" aria-label="Calories pour maintien" aria-live="polite"></span>
        </div>
        
        <!-- Prise de masse -->
        <div class="bg-white/80 rounded-xl shadow p-4 flex flex-col items-center">
            <span class="font-semibold text-gray-700">Prise de masse</span>
            <span class="text-purple-600 font-bold text-lg" id="masse-value" x-text="caloriesMasse + ' kcal/jour'"
                  role="status" aria-label="Calories pour prise de masse" aria-live="polite"></span>
        </div>
    </div>
</div>
