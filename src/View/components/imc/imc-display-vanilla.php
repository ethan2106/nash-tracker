<?php

/**
 * Composant: Affichage visuel IMC.
 *
 * @description Score IMC avec barre colorée et catégories
 * @requires imc-calculator.js (Vanilla JS)
 *
 * @var float $imcValue Valeur IMC calculée
 * @var int $imcPoints Points IMC (0-25)
 * @var string $imcAdvice Conseil personnalisé
 * @var callable $escape Fonction d'échappement HTML
 */

declare(strict_types=1);

// Calculer les infos IMC pour l'affichage initial
$imcInfo = ['cat' => 'Normal', 'color' => '#22c55e'];
if ($imcValue < 18.5)
{
    $imcInfo = ['cat' => 'Maigreur', 'color' => '#3b82f6'];
} elseif ($imcValue >= 25 && $imcValue < 30)
{
    $imcInfo = ['cat' => 'Surpoids', 'color' => '#f97316'];
} elseif ($imcValue >= 30)
{
    $imcInfo = ['cat' => 'Obésité', 'color' => '#ef4444'];
}

// Position du marqueur
$minIMC = 16;
$maxIMC = 40;
$markerPosition = max(0, min(100, (($imcValue - $minIMC) / ($maxIMC - $minIMC)) * 100));
?>
<!-- ============================================================
     SECTION IMC VISUEL
     - Score IMC en gros
     - Badge catégorie coloré
     - Barre de progression avec marqueur
     - Grille des 5 catégories IMC
     ============================================================ -->
<div class="bg-blue-50/60 rounded-2xl shadow-lg p-8 flex flex-col items-center gap-4">
    
    <!-- ===== SCORE IMC ===== -->
    <span class="imc-value font-bold text-5xl text-blue-400 transition-colors duration-500"
          title="Votre indice de masse corporelle"
          role="status"
          aria-label="Indice de masse corporelle"
          aria-live="polite"><?= number_format($imcValue, 1); ?></span>
    
    <!-- ===== BADGE CATÉGORIE ===== -->
    <span class="imc-category px-4 py-1 rounded-full font-semibold transition-colors duration-500"
          style="background-color: <?= $imcInfo['color']; ?>20; color: <?= $imcInfo['color']; ?>;"
          title="Catégorie IMC"><?= $escape($imcInfo['cat']); ?></span>
    
    <!-- ===== POINTS ET CONSEIL ===== -->
    <div class="mt-2 text-center">
        <p class="text-sm text-gray-700 font-semibold">Points IMC: <span class="text-blue-700">
            <?= (int)$imcPoints; ?>/25
        </span></p>
        <?php if (!empty($imcAdvice))
        { ?>
        <p class="text-xs text-gray-600 mt-1">
            <i class="fa-solid fa-circle-info text-blue-400 mr-1"></i><?= $escape($imcAdvice); ?>
        </p>
        <?php } ?>
    </div>
    
    <!-- ===== BARRE DE PROGRESSION IMC ===== -->
    <div class="w-full mt-4">
        <div class="h-2.5 md:h-4 rounded-xl bg-gradient-to-r from-blue-300 via-green-300 via-yellow-300 via-orange-300 to-red-400 relative overflow-hidden"
            role="progressbar"
            aria-valuemin="16"
            aria-valuemax="40"
            aria-label="Barre IMC">
            <!-- Marqueur IMC dynamique -->
            <div id="imc-marker" class="absolute top-0 h-2.5 md:h-4 w-2 rounded transition-all duration-700"
                style="left: <?= $markerPosition; ?>%; background-color: <?= $imcInfo['color']; ?>; box-shadow: 0 0 8px 2px <?= $imcInfo['color']; ?>;"
                aria-label="Marqueur IMC"></div>
        </div>
    </div>
    
    <!-- ===== GRILLE CATÉGORIES IMC ===== -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mt-4">
        <div class="bg-blue-50 border-l-4 border-blue-400 rounded-lg p-2 text-center">
            <div class="text-blue-700 font-bold text-sm">< 18.5</div>
            <div class="text-blue-600 text-xs font-medium mt-1">Maigreur</div>
        </div>
        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-2 text-center">
            <div class="text-green-700 font-bold text-sm">18.5 - 25</div>
            <div class="text-green-600 text-xs font-medium mt-1">Normal</div>
        </div>
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-2 text-center">
            <div class="text-yellow-700 font-bold text-sm">25 - 30</div>
            <div class="text-yellow-600 text-xs font-medium mt-1">Surpoids</div>
        </div>
        <div class="bg-orange-50 border-l-4 border-orange-500 rounded-lg p-2 text-center">
            <div class="text-orange-700 font-bold text-sm">30 - 35</div>
            <div class="text-orange-600 text-xs font-medium mt-1">Obésité I</div>
        </div>
        <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-2 text-center">
            <div class="text-red-700 font-bold text-sm">> 35</div>
            <div class="text-red-600 text-xs font-medium mt-1">Obésité II+</div>
        </div>
    </div>
</div>
