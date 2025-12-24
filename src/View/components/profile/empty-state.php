<?php
/**
 * Fallback Empty State - Message pour utilisateurs sans objectifs configurés.
 */
?>
<div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl p-12 text-center border border-white/50">
    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
        <i class="fa-solid fa-scale-balanced text-white text-3xl"></i>
    </div>
    <h3 class="text-3xl font-bold text-gray-800 mb-4">Configurez Votre Profil Santé</h3>
    <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
        Pour accéder à votre tableau de bord personnalisé, vous devez d'abord calculer votre IMC et définir vos objectifs nutritionnels.
    </p>
    <a href="?page=imc" class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold text-lg rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all">
        <i class="fa-solid fa-calculator"></i>
        Calculer Mon IMC
    </a>
</div>
