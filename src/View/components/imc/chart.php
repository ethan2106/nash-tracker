<?php

/**
 * Composant: Graphique évolution IMC.
 *
 * @description Section graphique Chart.js pour l'évolution IMC, Calories et Macros
 */

declare(strict_types=1);
?>
<!-- ============================================================
     SECTION GRAPHIQUE ÉVOLUTION
     - Canvas Chart.js pour l'évolution IMC/Calories/Macros
     - Lien retour vers profil
     ============================================================ -->
<div class="mt-8 flex flex-col gap-8">
    <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-2xl border border-blue-100 p-8 flex flex-col gap-6">
        
        <!-- ===== EN-TÊTE GRAPHIQUE ===== -->
        <div class="flex items-center gap-3 mb-4">
            <i class="fa-solid fa-chart-line text-blue-400 text-3xl drop-shadow"></i>
            <h2 class="text-2xl font-bold text-gray-800">Évolution IMC, Calories & Macros</h2>
        </div>
        
        <!-- ===== CANVAS CHART.JS ===== -->
        <div class="w-full min-h-[220px] flex items-center justify-center bg-gradient-to-r from-blue-50 via-green-50 to-yellow-50 rounded-2xl shadow-inner">
            <canvas id="imcChart" width="600" height="220"></canvas>
        </div>
    </div>
</div>

<!-- ===== LIEN RETOUR PROFIL ===== -->
<a href="?page=profile" class="mt-8 flex items-center gap-2 px-5 py-2 rounded-xl bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold shadow-md transition-all border border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-300">
    <i class="fa-solid fa-user-circle"></i>Retour au profil
</a>
