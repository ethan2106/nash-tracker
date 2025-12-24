<?php
/**
 * Card IMC - Affichage de l'indice de masse corporelle.
 * @param float $imc - Valeur de l'IMC
 */
$imcLabel = $imc < 18.5 ? 'Sous-poids' : ($imc < 25 ? 'Normal' : ($imc < 30 ? 'Surpoids' : 'Obésité'));
?>
<div class="bg-white/70 backdrop-blur-md rounded-2xl p-4 md:p-6 text-center shadow-lg border border-white/50 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group cursor-pointer" title="Votre Indice de Masse Corporelle">
    <div class="mb-3">
        <i class="fa-solid fa-weight-scale text-blue-500 text-2xl group-hover:scale-110 transition-transform duration-300"></i>
    </div>
    <div class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-1" x-text="counters.imc.current"></div>
    <div class="text-xs md:text-sm text-gray-600 font-medium">Votre IMC</div>
    <div class="text-xs text-gray-500 mt-1"><?= $imcLabel; ?></div>
</div>
