<?php
/**
 * Card Calories - Affichage de l'objectif calorique journalier.
 */
?>
<div class="bg-white/70 backdrop-blur-md rounded-2xl p-4 md:p-6 text-center shadow-lg border border-white/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group cursor-pointer">
    <div class="mb-3">
        <i class="fa-solid fa-fire text-green-500 text-2xl group-hover:scale-110 transition-transform duration-300 animate-pulse"></i>
    </div>
    <div class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent mb-1" x-text="counters.calories.current">0</div>
    <div class="text-xs md:text-sm text-gray-600 font-medium">Calories/jour</div>
    <div class="text-xs text-gray-500 mt-1">Objectif personnalisé</div>
</div>
