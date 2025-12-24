<?php
/**
 * Composant Mesures Physiques - Affichage des données corporelles.
 * @param array $objectifs - Objectifs et mesures de l'utilisateur
 */
?>
<div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl p-8 border border-white/50 hover:shadow-2xl transition-all duration-300">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
            <i class="fa-solid fa-chart-line text-white text-xl"></i>
        </div>
        <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Mesures Physiques</h3>
    </div>
    <div class="space-y-4">
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl hover:shadow-md hover:scale-[1.02] transition-all duration-300 border border-blue-200 group cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/80 flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                    <i class="fa-solid fa-ruler-vertical text-blue-600 text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="font-semibold text-gray-700">Taille</span>
            </div>
            <span class="text-2xl font-bold text-blue-600"><?= e($objectifs['taille']); ?> <span class="text-sm text-blue-500">cm</span></span>
        </div>
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-xl hover:shadow-md hover:scale-[1.02] transition-all duration-300 border border-green-200 group cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/80 flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                    <i class="fa-solid fa-weight-scale text-green-600 text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="font-semibold text-gray-700">Poids</span>
            </div>
            <span class="text-2xl font-bold text-green-600"><?= htmlspecialchars($objectifs['poids']); ?> <span class="text-sm text-green-500">kg</span></span>
        </div>
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl hover:shadow-md hover:scale-[1.02] transition-all duration-300 border border-purple-200 group cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/80 flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                    <i class="fa-solid fa-calculator text-purple-600 text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="font-semibold text-gray-700">IMC</span>
            </div>
            <span class="text-2xl font-bold text-purple-600"><?= number_format($objectifs['imc'], 1, ',', ' '); ?></span>
        </div>
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl hover:shadow-md hover:scale-[1.02] transition-all duration-300 border border-orange-200 group cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/80 flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                    <i class="fa-solid fa-venus-mars text-orange-600 text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="font-semibold text-gray-700">Genre</span>
            </div>
            <span class="text-lg font-semibold text-orange-600 capitalize"><?= htmlspecialchars($objectifs['sexe']); ?></span>
        </div>
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-pink-50 to-pink-100 rounded-xl hover:shadow-md hover:scale-[1.02] transition-all duration-300 border border-pink-200 group cursor-pointer">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/80 flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                    <i class="fa-solid fa-person-running text-pink-600 text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="font-semibold text-gray-700">Activité</span>
            </div>
            <span class="text-lg font-semibold text-pink-600 capitalize"><?= htmlspecialchars($objectifs['activite']); ?></span>
        </div>
    </div>
</div>
